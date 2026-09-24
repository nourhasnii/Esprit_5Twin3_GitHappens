<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $logs = AuditLog::with('actor')->when($request->filled('search'), fn ($query) => $query->where('action', 'like', '%' . $request->string('search') . '%'))->latest()->paginate(25)->withQueryString();
        return view('admin.audit.index', compact('logs'));
    }
}