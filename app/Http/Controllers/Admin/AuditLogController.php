<?php

namespace App\Http\Controllers\Admin;

use App\Models\AuditLog;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class AuditLogController extends Controller
{
    /**
     * Display a listing of the audit logs with advanced filtering.
     */
    public function index(Request $request)
    {
        // Build base query, eager-load 'user' relationship
        $query = AuditLog::with('user');

        // 1) Filter by user_id (if provided)
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        // 2) Filter by 'type' (e.g. create, update, delete, etc.)
        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        // 3) Filter by 'entity_type' (the fully-qualified model class, e.g. App\Models\Course)
        if ($request->filled('entity_type')) {
            $query->where('entity_type', $request->input('entity_type'));
        }

        // 4) Search in 'description' field (optional keyword)
        if ($request->filled('keyword')) {
            $keyword = $request->input('keyword');
            $query->where('description', 'like', "%{$keyword}%");
        }

        // 5) Optional date range: from_date / to_date on 'created_at'
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->input('from_date'));
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->input('to_date'));
        }

        // Get paginated results (adjust per-page count as needed)
        $logs = $query->orderBy('id', 'desc')->paginate(15);

        // For the <select> to filter by entity type:
        // group by distinct entity_type from the audit_logs table
     
        $entityTypes = AuditLog::select('entity_type')
        ->groupBy('entity_type')
        ->pluck('entity_type')
        ->map(function ($etype) {
            // Remove the App\Models\ prefix
            return Str::replaceFirst('App\\Models\\', '', $etype);
        });

        // Return a view with the logs and the distinct entityTypes for filtering
        return view('admin.audit_logs.index', compact('logs', 'entityTypes'));
    }
}
