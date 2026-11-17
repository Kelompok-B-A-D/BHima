<?php

namespace App\Http\Controllers;

use App\Models\ServiceContent;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class ServiceContentController extends Controller
{
    private function authorizeManager(Request $request): void
    {
        abort_unless($request->user()?->is_active && $request->user()?->role === 'admin', 403);
    }

    public function index(Request $request)
    {
        return $this->listing($request, false);
    }

    public function manage(Request $request)
    {
        $this->authorizeManager($request);

        return $this->listing($request, true);
    }

    private function listing(Request $request, bool $manage)
    {
        $filters = $request->validate([
            'q' => 'nullable|string|max:200', 'category' => 'nullable|string|max:100',
            'agency' => 'nullable|string|max:200', 'type' => 'nullable|in:daerah,umum',
            'status' => 'nullable|in:Draf,Terbit',
        ]);
        $base = ServiceContent::query();
        if (! $manage) {
            $base->published();
        }
        $options = [];
        foreach (['category', 'agency'] as $field) {
            $options[$field] = (clone $base)->select('payload->'.($field === 'category' ? 'service_category' : $field).' as value')->distinct()->orderBy('value')->pluck('value')->filter()->values();
        }
        $query = clone $base;
        if ($keyword = trim($filters['q'] ?? '')) {
            $query->where(function ($query) use ($keyword) {
                foreach (['title', 'body', 'agency', 'category', 'service_category'] as $field) {
                    $query->orWhere('payload->'.$field, 'like', '%'.$keyword.'%');
                }
            });
        }
        foreach (['category', 'agency', 'type'] as $field) {
            if (! empty($filters[$field])) {
                $query->where('payload->'.($field === 'category' ? 'service_category' : $field), $filters[$field]);
            }
        }
        if ($manage && ! empty($filters['status'])) {
            $query->where('payload->status', $filters['status']);
        }
        $records = $query->orderByDesc('updated_at')->orderByDesc('id')->paginate(12)->withQueryString();

        return view('services.index', compact('records', 'filters', 'options', 'manage'));
    }

    public function show(string $key)
    {
        $record = ServiceContent::published()->where('record_key', $key)->firstOrFail();

        return view('services.show', compact('record'));
    }

    public function create(Request $request)
    {
        $this->authorizeManager($request);

        return view('services.form', ['record' => null]);
    }

    public function edit(Request $request, string $key)
    {
        $this->authorizeManager($request);

        return view('services.form', ['record' => ServiceContent::where('record_key', $key)->firstOrFail()]);
    }

    public function store(Request $request, ?string $key = null)
    {
        $this->authorizeManager($request);
        $input = $request->validate([
            'title' => 'required|string|max:200', 'category' => 'required|in:SOP,Panduan,Pengumuman,Informasi',
            'service_category' => 'required|string|max:100', 'agency' => 'required|string|max:200',
            'type' => 'required|in:daerah,umum', 'body' => 'required|string|max:50000',
            'requirements' => 'nullable|string|max:20000', 'procedure' => 'nullable|string|max:20000',
            'duration' => 'nullable|string|max:200', 'cost' => 'nullable|string|max:200',
            'status' => 'required|in:Draf,Terbit',
            'file' => 'nullable|file|extensions:pdf|mimes:pdf|mimetypes:application/pdf|max:10240',
        ], ['required' => ':attribute wajib diisi.', 'string' => ':attribute harus berupa teks.', 'in' => 'Pilihan :attribute tidak valid.', 'max.string' => ':attribute maksimal :max karakter.', 'file' => 'Dokumen gagal diunggah.', 'file.max' => 'Ukuran PDF maksimal 10 MB.', 'file.mimes' => 'Dokumen harus berupa PDF.', 'file.mimetypes' => 'Dokumen harus berupa PDF.', 'file.extensions' => 'Ekstensi dokumen harus .pdf.'], [
            'title' => 'judul', 'service_category' => 'kategori layanan', 'agency' => 'instansi', 'body' => 'deskripsi',
        ]);
        $newPath = null;
        try {
            if ($request->hasFile('file')) {
                $newPath = $request->file('file')->store('services', 'local');
                if (! $newPath) {
                    throw ValidationException::withMessages(['file' => 'Unggah PDF gagal. Silakan coba kembali.']);
                }
            }
            $oldPath = DB::transaction(function () use ($request, $input, $key, $newPath) {
                $record = $key ? ServiceContent::where('record_key', $key)->lockForUpdate()->firstOrFail() : new ServiceContent(['collection' => 'contents', 'record_key' => (string) Str::uuid()]);
                $oldPath = $record->file_path;
                $payload = array_replace($record->payload ?? [], collect($input)->except('file')->all());
                $payload['id'] = $record->record_key;
                $payload['updated'] = now()->toDateString();
                if ($newPath) {
                    $payload['filename'] = basename(str_replace('\\', '/', $request->file('file')->getClientOriginalName()));
                    $record->file_path = $newPath;
                }
                $record->payload = $payload;
                $record->save();
                DB::table('dashboard_activities')->insert(['user_id' => $request->user()->id, 'text' => 'Memperbarui contents '.$record->record_key, 'created_at' => now(), 'updated_at' => now()]);

                return $newPath ? $oldPath : null;
            });
        } catch (Throwable $exception) {
            if ($newPath) {
                Storage::disk('local')->delete($newPath);
            }
            if ($exception instanceof ModelNotFoundException || $exception instanceof ValidationException || $exception instanceof HttpExceptionInterface) {
                throw $exception;
            }
            report($exception);

            return back()->withInput($request->except('file'))->withErrors(['save' => 'Informasi belum berhasil disimpan. Silakan coba kembali.']);
        }

        $this->removeUnusedFile($oldPath);

        return redirect()->route('services.manage')->with('success', 'Informasi layanan berhasil disimpan.');
    }

    public function destroy(Request $request, string $key)
    {
        $this->authorizeManager($request);
        $oldPath = DB::transaction(function () use ($request, $key) {
            $record = ServiceContent::where('record_key', $key)->lockForUpdate()->firstOrFail();
            $oldPath = $record->file_path;
            $record->delete();
            DB::table('dashboard_activities')->insert(['user_id' => $request->user()->id, 'text' => 'Menghapus contents '.$key, 'created_at' => now(), 'updated_at' => now()]);

            return $oldPath;
        });
        $this->removeUnusedFile($oldPath);

        return redirect()->route('services.manage')->with('success', 'Informasi layanan berhasil dihapus.');
    }

    private function removeUnusedFile(?string $path): void
    {
        if ($path && ! DB::table('dashboard_records')->where('file_path', $path)->exists()) {
            if (! Storage::disk('local')->delete($path)) {
                report(new \RuntimeException('Pembersihan dokumen layanan tertunda.'));
            }
        }
    }

    public function download(Request $request, string $key)
    {
        $record = ServiceContent::where('record_key', $key)->firstOrFail();
        abort_unless(($record->payload['status'] ?? '') === 'Terbit' || ($request->user()?->is_active && $request->user()?->role === 'admin'), 404);
        if (! $record->file_path || ! Storage::disk('local')->exists($record->file_path)) {
            return response()->view('services.unavailable', [], 404);
        }

        return Storage::disk('local')->download($record->file_path, (Str::slug($record->payload['title']) ?: 'panduan').'.pdf', ['Content-Type' => 'application/pdf', 'X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'private, no-store']);
    }
}
