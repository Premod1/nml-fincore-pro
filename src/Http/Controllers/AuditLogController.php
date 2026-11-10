<?php

namespace Nml\FinCorePro\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Nml\FinCore\Models\AuditLog;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with('journalEntry')->orderByDesc('created_at')->orderByDesc('id');

        if ($request->filled('action')) {
            $query->where('action', $request->query('action'));
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->query('from_date'));
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->query('to_date'));
        }

        if ($request->filled('search')) {
            $search = '%' . $request->query('search') . '%';
            $query->where(function ($q) use ($search) {
                $q->where('action', 'like', $search)
                  ->orWhere('ip_address', 'like', $search)
                  ->orWhereHas('journalEntry', function ($entryQ) use ($search) {
                      $entryQ->where('entry_number', 'like', $search)
                             ->orWhere('reference', 'like', $search);
                  });
            });
        }

        $logs = $query->paginate(25)->withQueryString();
        $actions = AuditLog::select('action')->distinct()->pluck('action');

        return view('fincore-pro::pages.audit-trail.index', [
            'logs'    => $logs,
            'actions' => $actions,
        ]);
    }
}
