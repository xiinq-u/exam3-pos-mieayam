<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = AuditLog::with('user:id,name')
            ->latest()
            ->limit($request->integer('limit', 100))
            ->get();

        return response()->json(['data' => $logs]);
    }
}
