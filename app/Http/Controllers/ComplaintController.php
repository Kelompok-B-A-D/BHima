<?php

namespace App\Http\Controllers;

use App\Services\ComplaintService;
use Illuminate\Http\Request;

class ComplaintController extends Controller
{
    public function __construct(private readonly ComplaintService $complaints) {}

    public function create(Request $request)
    {
        $uuid = $request->query('session')
            ?: $request->session()->get('bhima_consultation_session_uuid');

        return view('complaint.create', [
            'prefill' => $this->complaints->prescreeningFor($uuid),
        ]);
    }

    public function store(Request $request)
    {
        // TODO: validasi dan simpan ke dashboard_records (collection 'complaints')
        return redirect()->route('complaint.create');
    }

    public function trackForm()
    {
        return view('complaint.track');
    }

    public function track(Request $request)
    {
        // TODO: cari nomor tiket dan tampilkan status
        return redirect()->route('complaint.track');
    }
}