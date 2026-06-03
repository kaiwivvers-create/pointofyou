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

            // Check if model uses SoftDeletes before calling withTrashed
            $model = class_exists($modelType) ? new $modelType() : null;
            if ($model && in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses($model))) {
                $model = $modelType::withTrashed()->find($modelId);
            } else {
                $model = $modelType::find($modelId);
            }

            if (!$model) {
                return back()->with('error', 'Model not found.');
            }

            if ($activityLog->action === 'deleted') {
                if (method_exists($model, 'restore')) {
                    $model->restore();
                    $this->logActivity('restored', $model, null, $oldValues);
                } else {
                    return back()->with('error', 'This model does not support restore (no SoftDeletes).');
                }
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
        // Only allow permanent deletion if the log is for a deleted item
        if ($activityLog->action !== 'deleted') {
            return back()->with('error', 'Can only permanently delete logs for deleted items.');
        }

        try {
            DB::beginTransaction();

            $modelType = $activityLog->model_type;
            $modelId = $activityLog->model_id;

            if (!$modelType || !$modelId) {
                return back()->with('error', 'Cannot permanently delete. Missing model data.');
            }

            // Check if model uses SoftDeletes and is trashed
            $model = class_exists($modelType) ? new $modelType() : null;
            if ($model && in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses($model))) {
                $trashedModel = $modelType::withTrashed()->find($modelId);
                if ($trashedModel && $trashedModel->trashed()) {
                    // Permanently delete the model
                    $trashedModel->forceDelete();
                } else {
                    return back()->with('error', 'Model is not deleted (not in trash).');
                }
            } else {
                return back()->with('error', 'This model does not support soft delete.');
            }

            // Also delete the activity log
            $activityLog->forceDelete();

            DB::commit();
            return back()->with('success', 'Item and log permanently deleted.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to permanently delete: ' . $e->getMessage());
        }
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
                'reverted_from_log_id' => request()->route('activityLog')?->id,
            ],
        ]);
    }
}
