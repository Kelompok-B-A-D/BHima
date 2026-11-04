<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class DashboardDataController extends Controller
{
    private const COLLECTIONS = ['complaints', 'contents', 'documents', 'budgets', 'accounts'];

    private const STATUSES = ['Baru', 'Menunggu Verifikasi', 'Menunggu Kelengkapan', 'Dalam Proses', 'Menunggu Disposisi', 'Selesai', 'Ditolak'];

    public function index(Request $request)
    {
        $admin = $request->user()->role === 'admin';
        $result = array_fill_keys([...self::COLLECTIONS, 'searches', 'activities'], []);
        $records = DB::table('dashboard_records')->when(! $admin, fn ($q) => $q->where('collection', 'complaints')->where('assigned_to', $request->user()->id))->get();
        foreach ($records as $record) {
            $result[$record->collection][] = json_decode($record->payload, true);
        }
        if ($admin) {
            $result['accounts'] = User::all()->map(fn ($u) => ['id' => (string) $u->id, 'name' => $u->name, 'email' => $u->email, 'role' => $u->role, 'status' => $u->is_active ? 'Aktif' : 'Nonaktif', 'created' => $u->created_at->toDateString()])->all();
        }
        $result['activities'] = DB::table('dashboard_activities')->when(! $admin, fn ($q) => $q->where('user_id', $request->user()->id))->orderByDesc('id')->limit(200)->get()->map(fn ($a) => ['date' => $a->created_at, 'text' => $a->text])->all();

        return response()->json($result);
    }

    public function save(Request $request, string $collection)
    {
        abort_unless(in_array($collection, self::COLLECTIONS, true), 404);
        $admin = $request->user()->role === 'admin';
        abort_unless($admin || $collection === 'complaints', 403);
        if ($collection === 'accounts') {
            return $this->saveAccount($request);
        }
        $base = $request->validate(['id' => 'required|string|max:80|regex:/^[A-Za-z0-9_-]+$/']);
        $rules = match ($collection) {
            'contents' => ['title' => 'required|string|max:200', 'category' => 'required|in:SOP,Panduan,Pengumuman,Informasi', 'body' => 'required|string|max:50000', 'status' => 'required|in:Draf,Terbit'],
            'documents' => ['title' => 'required|string|max:200', 'number' => 'required|string|max:100', 'year' => 'required|integer|min:1900|max:2100', 'category' => 'required|string|max:100', 'summary' => 'required|string|max:10000', 'file' => 'nullable|file|mimes:pdf|mimetypes:application/pdf|max:10240'],
            'budgets' => ['title' => 'required|string|max:200', 'year' => 'required|integer|min:1900|max:2100', 'allocation' => 'required|numeric|min:0|max:999999999999', 'realized' => 'required|numeric|min:0|lte:allocation'],
            'complaints' => ['action' => 'required|in:verify,complete,reject,dispatch,status,note,assign', 'note' => 'required|string|min:5|max:5000', 'status' => ['nullable', Rule::in(self::STATUSES)], 'agency' => 'required_if:action,dispatch|nullable|string|max:200', 'assigned_to' => 'required_if:action,assign|nullable|integer|exists:users,id'],
        };
        $input = $request->validate($rules);
        DB::transaction(function () use ($request, $collection, $base, $input, $admin) {
            // A user row lock serializes capacity checks and per-record changes for this internal workspace.
            DB::table('users')->orderBy('id')->lockForUpdate()->get(['id']);
            $record = DB::table('dashboard_records')->where('collection', $collection)->where('record_key', $base['id'])->lockForUpdate()->first();
            $old = $record ? json_decode($record->payload, true) : [];
            $assigned = $record?->assigned_to;
            if ($collection === 'complaints') {
                abort_unless($record && ($admin || $assigned === $request->user()->id), 403);
                $action = $input['action'];
                if ($action === 'assign') {
                    abort_unless($admin, 403);
                    $assignee = User::findOrFail($input['assigned_to']);
                    abort_unless($assignee->is_active && $assignee->role === 'verifikator', 422);
                    $assigned = $assignee->id;
                    $old['officer'] = $assignee->name;
                } else {
                    $status = match ($action) {
                        'verify' => 'Menunggu Disposisi', 'complete' => 'Menunggu Kelengkapan', 'reject' => 'Ditolak', 'dispatch' => 'Dalam Proses', 'status' => $input['status'] ?? null, default => $old['status'],
                    };
                    if ($action === 'status' && ! in_array($status, ['Dalam Proses', 'Selesai'], true)) {
                        throw ValidationException::withMessages(['status' => 'Pilih status tindak lanjut yang tersedia.']);
                    }
                    $allowed = match ($action) {
                        'verify', 'complete', 'reject' => ['Baru', 'Menunggu Verifikasi', 'Menunggu Kelengkapan'],
                        'dispatch' => ['Menunggu Disposisi'],
                        'status' => ['Dalam Proses'],
                        default => self::STATUSES,
                    };
                    if (! in_array($old['status'], $allowed, true)) {
                        throw ValidationException::withMessages(['action' => 'Tindakan tidak sesuai tahapan laporan. Muat ulang data.']);
                    }
                    $old['status'] = $status;
                    if ($action === 'dispatch') {
                        $old['agency'] = $input['agency'];
                    }
                }
                $old['notes'][] = ['date' => now()->toIso8601String(), 'text' => $input['note'], 'by' => $request->user()->name];
                $old['history'][] = ['date' => now()->toIso8601String(), 'text' => $input['action'].' · '.$old['status']];
                $payload = $old;
            } else {
                $payload = [...$old, ...collect($input)->except('file')->all(), 'id' => $base['id'], 'updated' => now()->toDateString()];
            }
            $filePath = $record?->file_path;
            if ($collection === 'documents') {
                if (! $record && DB::table('dashboard_records')->where('collection', 'documents')->count() >= 100) {
                    throw ValidationException::withMessages(['file' => 'Kapasitas maksimal 100 dokumen.']);
                }
                if (! $record && ! $request->hasFile('file')) {
                    throw ValidationException::withMessages(['file' => 'Dokumen PDF wajib diunggah.']);
                }
                if ($request->hasFile('file')) {
                    $filePath = $request->file('file')->store('jdih', 'local');
                    $payload['filename'] = $request->file('file')->getClientOriginalName();
                    $payload['status'] = 'Menunggu';
                    $payload['extraction'] = 'Menunggu';
                }
            }
            DB::table('dashboard_records')->updateOrInsert(['collection' => $collection, 'record_key' => $base['id']], ['payload' => json_encode($payload), 'assigned_to' => $assigned, 'file_path' => $filePath, 'updated_at' => now(), 'created_at' => $record?->created_at ?? now()]);
            $this->audit($request, 'Memperbarui '.$collection.' '.$base['id']);
        });

        return response()->json(['message' => 'Perubahan tersimpan.']);
    }

    private function saveAccount(Request $request)
    {
        $input = $request->validate(['id' => 'required|string|max:80', 'name' => 'required|string|max:255', 'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore(ctype_digit((string) $request->input('id')) ? (int) $request->input('id') : null)], 'role' => 'required|in:admin,verifikator', 'status' => 'required|in:Aktif,Nonaktif', 'password' => 'nullable|string|min:12|max:255']);
        DB::transaction(function () use ($request, $input) {
            DB::table('users')->orderBy('id')->lockForUpdate()->get(['id']);
            $user = ctype_digit($input['id']) ? User::findOrFail($input['id']) : new User;
            if ($user->id === $request->user()->id && ($input['role'] !== 'admin' || $input['status'] !== 'Aktif')) {
                throw ValidationException::withMessages(['status' => 'Anda tidak dapat mencabut akses administrator sendiri.']);
            }
            if (! $user->exists && empty($input['password'])) {
                throw ValidationException::withMessages(['password' => 'Kata sandi diperlukan untuk akun baru.']);
            }
            $user->name = $input['name'];
            $user->email = $input['email'];
            $user->role = $input['role'];
            $user->is_active = $input['status'] === 'Aktif';
            if (! empty($input['password'])) {
                $user->password = Hash::make($input['password']);
            }
            $user->save();
            $this->audit($request, 'Memperbarui akun '.$user->id);
        });

        return response()->json(['message' => 'Akun tersimpan.']);
    }

    public function destroy(Request $request, string $collection, string $key)
    {
        abort_unless($request->user()->role === 'admin', 403);
        abort_unless(in_array($collection, ['contents', 'documents', 'budgets'], true), 403);
        DB::transaction(function () use ($request, $collection, $key) {
            $deleted = DB::table('dashboard_records')->where('collection', $collection)->where('record_key', $key)->delete();
            abort_unless($deleted, 404);
            $this->audit($request, 'Menghapus '.$collection.' '.$key);
        });

        return response()->json(['message' => 'Data dihapus.']);
    }

    public function download(Request $request, string $key)
    {
        abort_unless($request->user()->role === 'admin', 403);
        $record = DB::table('dashboard_records')->where('collection', 'documents')->where('record_key', $key)->first();
        abort_unless($record && $record->file_path && Storage::disk('local')->exists($record->file_path), 404);

        return Storage::disk('local')->download($record->file_path, basename(json_decode($record->payload, true)['filename']));
    }

    private function audit(Request $request, string $text): void
    {
        DB::table('dashboard_activities')->insert(['user_id' => $request->user()->id, 'text' => $text, 'created_at' => now(), 'updated_at' => now()]);
    }
}
