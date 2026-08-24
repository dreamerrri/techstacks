<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with('user')->latest();

        if ($request->filled('module')) {
            $query->byModule($request->module);
        }

        if ($request->filled('action')) {
            $query->byAction($request->action);
        }

        if ($request->filled('user_id')) {
            $query->byUser($request->user_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->paginate(67)->onEachSide(1);
   

        // values() reindexes after sort() so these serialize as JSON arrays,
        // not objects (objects crash .map() on the frontend)
        $modules = AuditLog::distinct()->pluck('module')->filter()->sort()->values();
        $actions = AuditLog::distinct()->pluck('action')->filter()->sort()->values();

        return Inertia::render('AuditLogs/Index', [
            'logs' => $logs,
            'modules' => $modules,
            'actions' => $actions,
            'filters' => $request->only(['module', 'action', 'user_id', 'date_from', 'date_to']),
        ]);
    }

    public function show(AuditLog $auditLog)
    {
        $auditLog->load('user');
        return Inertia::render('AuditLogs/Show', ['auditLog' => $auditLog]);
    }
}
