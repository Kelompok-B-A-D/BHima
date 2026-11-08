<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ComplaintController extends Controller
{
    public function create(Request $request)
    {
        return view('complaint.create');
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