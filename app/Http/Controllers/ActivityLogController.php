<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with('user')->latest();

        // Optional filtering by module (model_type)
        if ($request->filled('module')) {
            $query->where('model_type', 'like', '%' . $request->module . '%');
        }

        // Optional filtering by action
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        $logs = $query->paginate(20)->withQueryString();

        return view('activity_logs.index', compact('logs'));
    }
}
