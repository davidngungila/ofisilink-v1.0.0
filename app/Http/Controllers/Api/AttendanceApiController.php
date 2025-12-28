<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\Request;

class AttendanceApiController extends Controller
{
    /**
     * Get attendance records
     * 
     * Query Parameters:
     * - date: YYYY-MM-DD
     * - user_id: Filter by user ID
     * - enroll_id: Filter by enroll ID
     * - date_from: Start date
     * - date_to: End date
     * - status: Filter by status
     */
    public function index(Request $request)
    {
        $query = Attendance::with('user');

        // Filter by date
        if ($request->has('date')) {
            $query->whereDate('attendance_date', $request->date);
        }

        // Filter by date range
        if ($request->has('date_from') && $request->has('date_to')) {
            $query->whereBetween('attendance_date', [
                $request->date_from,
                $request->date_to
            ]);
        }

        // Filter by user
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by enroll_id
        if ($request->has('enroll_id')) {
            $query->where('enroll_id', $request->enroll_id);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $attendances = $query->orderBy('attendance_date', 'desc')
            ->orderBy('check_in_time', 'desc')
            ->paginate($request->get('per_page', 50));

        return response()->json([
            'success' => true,
            'data' => $attendances->map(function($attendance) {
                return [
                    'id' => $attendance->id,
                    'user' => [
                        'id' => $attendance->user->id ?? null,
                        'name' => $attendance->user->name ?? 'Unknown',
                        'enroll_id' => $attendance->user->enroll_id ?? null,
                    ],
                    'attendance_date' => $attendance->attendance_date,
                    'check_in_time' => $attendance->check_in_time ? $attendance->check_in_time->format('Y-m-d H:i:s') : null,
                    'check_out_time' => $attendance->check_out_time ? $attendance->check_out_time->format('Y-m-d H:i:s') : null,
                    'status' => $attendance->status ?? '1',
                    'verify_mode' => $attendance->verify_mode ?? 'Fingerprint',
                    'device_ip' => $attendance->device_ip ?? null,
                ];
            }),
            'pagination' => [
                'current_page' => $attendances->currentPage(),
                'total' => $attendances->total(),
                'per_page' => $attendances->perPage(),
                'last_page' => $attendances->lastPage(),
            ]
        ]);
    }

    /**
     * Get single attendance record
     */
    public function show($id)
    {
        $attendance = Attendance::with('user')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $attendance->id,
                'user' => [
                    'id' => $attendance->user->id,
                    'name' => $attendance->user->name,
                    'enroll_id' => $attendance->user->enroll_id,
                ],
                'attendance_date' => $attendance->attendance_date,
                'check_in_time' => $attendance->check_in_time?->format('Y-m-d H:i:s'),
                'check_out_time' => $attendance->check_out_time?->format('Y-m-d H:i:s'),
                'status' => $attendance->status,
                'verify_mode' => $attendance->verify_mode,
                'device_ip' => $attendance->device_ip,
            ]
        ]);
    }

    /**
     * Get daily summary
     */
    public function daily($date)
    {
        $attendances = Attendance::with('user')
            ->whereDate('attendance_date', $date)
            ->get()
            ->groupBy('user_id')
            ->map(function ($group) {
                $attendance = $group->first();
                return [
                    'user' => [
                        'id' => $attendance->user->id,
                        'name' => $attendance->user->name,
                        'enroll_id' => $attendance->user->enroll_id,
                    ],
                    'date' => $attendance->attendance_date,
                    'check_in' => $attendance->check_in_time?->format('H:i:s'),
                    'check_out' => $attendance->check_out_time?->format('H:i:s'),
                    'duration' => $attendance->check_in_time && $attendance->check_out_time
                        ? $attendance->check_in_time->diff($attendance->check_out_time)->format('%h:%I:%S')
                        : null,
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'date' => $date,
            'data' => $attendances,
            'total' => $attendances->count(),
        ]);
    }
}




