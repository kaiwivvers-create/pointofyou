<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with('user')->latest();

        if ($request->filled('action')) {
            $query->forAction($request->action);
        }

        if ($request->filled('model_type')) {
            $query->where('model_type', $request->model_type);
        }

        if ($request->filled('user_id')) {
            $query->forUser($request->user_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('occurred_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('occurred_at', '<=', $request->date_to);
        }

        $logs = $query->paginate(50);

        $actions = ActivityLog::select('action')->distinct()->pluck('action');
        $modelTypes = ActivityLog::select('model_type')->distinct()->pluck('model_type');

        return view('super-admin.activity-logs.index', compact('logs', 'actions', 'modelTypes'));
    }

    public function show(ActivityLog $activityLog)
    {
        $activityLog->load('user');
        return view('super-admin.activity-logs.show', compact('activityLog'));
    }

    public function revert(ActivityLog $activityLog)
    {
        try {
            DB::beginTransaction();

            $modelType = $activityLog->model_type;
            $modelId = $activityLog->model_id;
            $oldValues = $activityLog->old_values;

            if (!$modelType || !$modelId || !$oldValues) {
                return back()->with('error', 'Cannot revert this log entry. Missing required data.');
            }

            $model = $modelType::withTrashed()->find($modelId);

            if (!$model) {
                return back()->with('error', 'Model not found.');
            }

            if ($activityLog->action === 'deleted') {
                $model->restore();
                $this->logActivity('restored', $model, null, $oldValues);
            } elseif ($activityLog->action === 'updated') {
                $model->update($oldValues);
                $this->logActivity('reverted', $model, $model->getAttributes(), $oldValues);
            } else {
                return back()->with('error', 'Cannot revert this action type.');
            }

            DB::commit();
            return back()->with('success', 'Record reverted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to revert: ' . $e->getMessage());
        }
    }

    public function destroy(ActivityLog $activityLog)
    {
        $activityLog->delete();
        return back()->with('success', 'Activity log deleted successfully.');
    }

    public function destroyPermanently(ActivityLog $activityLog)
    {
        $activityLog->forceDelete();
        return back()->with('success', 'Activity log permanently deleted.');
    }

    private function logActivity($action, $model, $oldValues = null, $newValues = null)
    {
        ActivityLog::create([
            'user_id' => auth()->id(),
            'ip_address' => request()->ip(),
            'action' => $action,
            'model_type' => get_class($model),
            'model_id' => $model->id,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'metadata' => [
                'reverted_from_log_id' => request()->route('activityLog'),
            ],
        ]);
    }
}
