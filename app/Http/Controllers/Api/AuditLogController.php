<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AuditLogResource;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    /**
     * Get audit logs with filters.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', AuditLog::class);

        $query = AuditLog::with(['user', 'auditable'])
            ->orderByDesc('created_at');

        // Filter by event
        if ($request->has('event')) {
            $query->ofEvent($request->event);
        }

        // Filter by user
        if ($request->has('user_id')) {
            $query->byUser($request->user_id);
        }

        // Filter by model
        if ($request->has('model_type') && $request->has('model_id')) {
            $query->forModel($request->model_type, $request->model_id);
        }

        // Filter by date range
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->between($request->start_date, $request->end_date);
        }

        $logs = $query->paginate($request->get('per_page', 50));

        return AuditLogResource::collection($logs);
    }

    /**
     * Get audit log details.
     */
    public function show(int $id)
    {
        $log = AuditLog::with(['user', 'auditable'])->findOrFail($id);

        $this->authorize('view', $log);

        return new AuditLogResource($log);
    }
}
