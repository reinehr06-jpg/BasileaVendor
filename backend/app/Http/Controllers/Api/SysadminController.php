<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\SysadminLog;

class SysadminController extends Controller
{
    private function verifyKey(Request $request)
    {
        $expectedKey = env('SYSADMIN_KEY', 'default_secret_key_123'); // Fallback for local testing
        $providedKey = $request->header('X-Sysadmin-Key');

        if (!$providedKey || $providedKey !== $expectedKey) {
            abort(401, 'Unauthorized Access');
        }
    }

    public function metrics(Request $request)
    {
        $this->verifyKey($request);

        // CPU Usage (sys_getloadavg might not work on Windows, fallback to 0)
        $cpu = function_exists('sys_getloadavg') ? sys_getloadavg()[0] : 0;
        
        // Memory Usage
        $memoryUsage = memory_get_usage(true) / 1024 / 1024; // MB
        
        // Disk Usage
        $diskFree = disk_free_space('/');
        $diskTotal = disk_total_space('/');
        $diskUsed = $diskTotal - $diskFree;
        $diskUsagePercent = $diskTotal > 0 ? round(($diskUsed / $diskTotal) * 100, 2) : 0;

        // DB Status
        $dbStatus = 'ok';
        try {
            DB::connection()->getPdo();
        } catch (\Exception $e) {
            $dbStatus = 'error';
        }

        return response()->json([
            'cpu' => round($cpu * 100, 2), // Assuming 1.0 = 100% load
            'memory_mb' => round($memoryUsage, 2),
            'disk_percent' => $diskUsagePercent,
            'db_status' => $dbStatus,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    public function logs(Request $request)
    {
        $this->verifyKey($request);

        $query = SysadminLog::query()->orderBy('created_at', 'desc');

        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        if ($request->filled('level')) {
            $query->where('level', $request->level);
        }

        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $query->where(function($q) use ($search) {
                $q->where('message', 'like', $search)
                  ->orWhere('payload', 'like', $search);
            });
        }

        if ($request->filled('start_date')) {
            $query->where('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->where('created_at', '<=', $request->end_date);
        }

        $logs = $query->limit(500)->get();

        return response()->json(['data' => $logs]);
    }

    public function ingest(Request $request)
    {
        $this->verifyKey($request);

        $validated = $request->validate([
            'source' => 'required|string',
            'level' => 'required|string',
            'message' => 'required|string',
            'payload' => 'nullable|array',
        ]);

        $log = SysadminLog::create([
            'source' => $validated['source'],
            'level' => $validated['level'],
            'message' => $validated['message'],
            'payload' => $validated['payload'] ?? [],
        ]);

        return response()->json(['success' => true, 'id' => $log->id]);
    }
}
