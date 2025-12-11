<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class LogController extends Controller
{
    public function index()
    {
        return view('admin.logs.index');
    }

    public function show($log)
    {
        $logPath = storage_path('logs/' . $log);
        
        if (!File::exists($logPath)) {
            abort(404, 'Log file not found');
        }

        $content = File::get($logPath);
        
        return view('admin.logs.show', compact('log', 'content'));
    }
}
