<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccessController extends Controller
{
    public function todayAccessSummary(Request $request)
    {
        $validToken = env('APPVERSION'); // Security token

        if ($request->query('token') !== $validToken) {
            return response()->json(['message' => 'Unauthorized access'], 403);
        }

        // Get today's total access count (excluding 'msjit')
        $todayCount = DB::table('sys_log')
            ->whereDate('created_at', Carbon::today())
            ->where('username', '<>', 'msjit')
            ->count();

        // Get today's unique users count (excluding 'msjit')
        $uniqueUsers = DB::table('sys_log')
            ->whereDate('created_at', Carbon::today())
            ->where('username', '<>', 'msjit')
            ->distinct('username')
            ->count('username');

        // Get total active users from `users` table
        $totalActiveUsers = DB::table('users')
            ->where('isactive', '1')
            ->count();

        // Get the latest access date today (excluding 'msjit')
        $latestAccess = DB::table('sys_log')
            ->where('username', '<>', 'msjit')
            ->latest('created_at')
            ->value('created_at');

        return response()->json([
            'date' => Carbon::today()->toDateString(),
            'total_access_count' => $todayCount,
            'unique_users_count' => "{$uniqueUsers}/{$totalActiveUsers}",
            'latest_access' => $latestAccess ? Carbon::parse($latestAccess)->toDateTimeString() : null
        ]);
    }
}
