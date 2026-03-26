<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Rest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Http\Requests\AttendanceUpdateRequest;

class AttendanceController extends Controller
{
    public function start(Request $request)
    {
        $user = Auth::user();
        $now = Carbon::now();

        $exists = Attendance::where('user_id', $user->id)
                            ->where('date', $now->toDateString())
                            ->exists();

        if ($exists) {
            return back()->with('error', '今日は既に出勤済みです');
        }

        Attendance::create([
            'user_id' => $user->id,
            'date' => $now->toDateString(),
            'punch_in' => $now->toTimeString(),
        ]);

        return redirect()->route('attendance.register');
    }

    public function index()
    {
        $userId = Auth::id();
        $today = Carbon::today()->toDateString();

        $attendance = Attendance::where('user_id', $userId)
            ->where('date', $today)
            ->first();

        if (!$attendance) {
            return view('attendance-register.before-work');
        }

        if ($attendance->punch_out) {
            return view('attendance-register.after-work');
        }

        $latestRest = $attendance->rests()->orderBy('id', 'desc')->first();
        if ($latestRest && !$latestRest->end_time) {
            return view('attendance-register.resting');
        }

        return view('attendance-register.working');
    }

    public function restStart()
    {
        $user = Auth::user();
        $today = Carbon::today();

        $attendance = Attendance::where('user_id', $user->id)
                                ->where('date', $today)
                                ->first();

        Rest::create([
            'attendance_id' => $attendance->id,
            'start_time' => Carbon::now()->toTimeString(),
        ]);

        return redirect()->route('attendance.register');
    }

    public function restEnd()
    {
        $user = Auth::user();
        $today = Carbon::today();

        $attendance = Attendance::where('user_id', $user->id)
                                ->where('date', $today)
                                ->first();

        if ($attendance) {
            $rest = Rest::where('attendance_id', $attendance->id)
                        ->whereNull('end_time')
                        ->latest()
                        ->first();

            if ($rest) {
                $rest->update([
                    'end_time' => Carbon::now()->toTimeString(),
                ]);
                return redirect()->route('attendance.register');
            }
        }

        return redirect()->route('attendance.register');
    }

    public function end()
    {
        $user = Auth::user();
        $today = Carbon::today();
        $now = Carbon::now();

        $attendance = Attendance::where('user_id', $user->id)
                                ->where('date', $today)
                                ->whereNull('punch_out')
                                ->first();

        if (!$attendance) {
            return redirect()->route('attendance.register');
        }

        $latestRest = Rest::where('attendance_id', $attendance->id)
                        ->whereNull('end_time')
                        ->first();
        if ($latestRest) {
            $latestRest->update(['end_time' => $now->toTimeString()]);
        }

        $attendance->update([
            'punch_out' => $now->toTimeString(),
        ]);

        return redirect()->route('attendance.register');
    }

    public function list(Request $request)
    {
        $month = $request->input('month', Carbon::now()->format('Y-m'));
        $currentDate = Carbon::parse($month);

        $prevMonth = $currentDate->copy()->subMonth()->format('Y-m');
        $nextMonth = $currentDate->copy()->addMonth()->format('Y-m');

        $attendances = Attendance::where('user_id', Auth::id())
            ->where('date', 'like', "$month%")
            ->with('rests')
            ->get()
            ->keyBy('date');

        $daysInMonth = $currentDate->daysInMonth;
        $calendar = [];

        for ($i = 1; $i <= $daysInMonth; $i++) {
            $date = $currentDate->copy()->day($i)->format('Y-m-d');

            if (isset($attendances[$date])) {
                $attendance = $attendances[$date];

                $totalRestSeconds = 0;
                foreach ($attendance->rests as $rest) {
                    if ($rest->start_time && $rest->end_time) {
                        $totalRestSeconds += Carbon::parse($rest->start_time)->diffInSeconds(Carbon::parse($rest->end_time));
                    }
                }
                $attendance->total_rest_time = gmdate("H:i", $totalRestSeconds);

                if ($attendance->punch_in && $attendance->punch_out) {
                    $totalWorkingSeconds = Carbon::parse($attendance->punch_in)->diffInSeconds(Carbon::parse($attendance->punch_out));
                    $attendance->actual_working_time = gmdate("H:i", max(0, $totalWorkingSeconds - $totalRestSeconds));
                } else {
                    $attendance->actual_working_time = '-';
                }

                $calendar[] = $attendance;
            } else {
                $calendar[] = (object)[
                    'id' => null,
                    'date' => $date,
                    'punch_in' => null,
                    'punch_out' => null,
                    'total_rest_time' => null,
                    'actual_working_time' => null,
                ];
            }
        }

        return view('attendance-register/attendance-list', [
            'calendar' => $calendar,
            'currentDate' => $currentDate,
            'prevMonth' => $prevMonth,
            'nextMonth' => $nextMonth
        ]);
    }

    public function edit($id)
    {
        $attendance = Attendance::with(['user', 'rests'])->findOrFail($id);

        return view('attendance-register/attendance-detail', compact('attendance'));
    }

    public function store(AttendanceUpdateRequest $request)
    {
        $user = auth()->user();
        $isAdmin = ($user->role === 1);

        $attendance = Attendance::create([
            'user_id' => $isAdmin ? $request->user_id : auth()->id(),
            'date' => $request->date,
            'punch_in' => $request->punch_in,
            'punch_out' => $request->punch_out,
            'remarks' => $request->remarks,
            'status' => $isAdmin ? 0 : 1,
            'applied_at' => $isAdmin ? null : now(),
        ]);

        if ($request->has('new_rests')) {
            foreach ($request->new_rests as $rest_time) {
                if (!empty($rest_time['start']) && !empty($rest_time['end'])) {
                    $attendance->rests()->create([
                        'start_time' => $rest_time['start'],
                        'end_time' => $rest_time['end'],
                    ]);
                }
            }
        }

        if ($isAdmin) {
            return redirect()->route('admin.attendance.list', ['id' => $attendance->user_id])
                    ->with('success', '勤怠情報を登録しました。');
        }

        return redirect()->route('attendance.list')->with('success', '勤怠登録の申請を出しました。');
    }

    public function update(AttendanceUpdateRequest $request, $id)
    {
        $attendance = Attendance::findOrFail($id);
        $user = auth()->user();

        $isAdmin = ($user->role === 1);

        $attendance->update([
            'punch_in' => $request->punch_in,
            'punch_out' => $request->punch_out,
            'remarks' => $request->remarks,
            'status' => $isAdmin ? 0 : 1,
            'applied_at' => now(),
        ]);

        if ($request->has('rests')) {
            foreach ($request->rests as $rest_id => $times) {
                Rest::where('id', $rest_id)->update([
                    'start_time' => $times['start'],
                    'end_time' => $times['end'],
                ]);
            }
        }

        if ($request->has('new_rests')) {
            foreach ($request->new_rests as $rest_time) {
                if (!empty($rest_time['start']) && !empty($rest_time['end'])) {
                    $attendance->rests()->create([
                        'start_time' => $rest_time['start'],
                        'end_time' => $rest_time['end'],
                    ]);
                }
            }
        }

        if ($isAdmin) {
            return redirect()->route('admin.attendance.list', ['id' => $attendance->user_id])
                ->with('success', '勤怠情報を更新しました。');
        }

        return redirect()->route('attendance.list')->with('success', '修正申請を送信しました。管理者の承認をお待ちください。');
    }

    public function approveForm(Request $request, $id)
    {
        $attendance = Attendance::with(['user', 'rests'])->findOrFail($id);

        return view('admin.attendance-correction-approve', compact('attendance'));
    }

    public function approve(Request $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        $attendance->status = 0;
        $attendance->save();

        return redirect()->route('admin.attendance.list', ['id' => $attendance->user_id])
                    ->with('success', '申請を承認しました。');
    }

    public function adminDailyIndex(Request $request)
    {
        $date = $request->query('date', now()->format('Y-m-d'));

        $attendances = Attendance::where('date', $date)
            ->with('user')
            ->get();

        return view('admin.daily-attendance', compact('attendances', 'date'));
    }

    public function correctionList(Request $request)
    {
        $tab = $request->query('tab', 'pending');
        $user = auth()->user();

        $query = Attendance::with('user');

        if ($user->role !== 1) {
            $query->where('user_id', $user->id);
        }

        if ($tab === 'approved') {
            $requests = $query->where('status', 0)
                            ->whereNotNull('applied_at')
                            ->orderBy('updated_at', 'desc')
                            ->get();
        } else {
            $requests = $query->where('status', 1)
                            ->orderBy('updated_at', 'desc')
                            ->get();
        }

        return view('attendance-register.correction-list', compact('requests', 'tab'));
    }

    public function staffList(Request $request)
    {
        $staff = User::where('role', 0)->get();

        return view('admin.staff-list', compact('staff'));
    }

    public function attendanceList(Request $request,$id)
    {
        $user = User::findOrFail($id);
        $monthParam = $request->query('month', Carbon::now()->format('Y-m'));
        $currentDate = Carbon::parse($monthParam)->startOfMonth();

        $prevMonth = $currentDate->copy()->subMonth()->format('Y-m');
        $nextMonth = $currentDate->copy()->addMonth()->format('Y-m');

        $daysInMonth = $currentDate->daysInMonth;
        $calendar = [];

        $attendances = Attendance::where('user_id', $id)
            ->whereYear('date', $currentDate->year)
            ->whereMonth('date', $currentDate->month)
            ->get()
            ->keyBy('date');

        for ($i = 0; $i < $daysInMonth; $i++) {
        $date = $currentDate->copy()->addDays($i)->format('Y-m-d');
        $calendar[] = $attendances->get($date) ?? (object)[
            'date' => $date,
            'punch_in' => null,
            'punch_out' => null,
            'total_rest_time' => null,
            'actual_working_time' => null,
            'id' => null
        ];
    }

        return view('admin.attendance-staff-list', compact(
            'user',
            'calendar',
            'currentDate',
            'prevMonth',
            'nextMonth'
        ));
    }

    public function showAttendanceDetail($id)
    {
        $attendance = Attendance::with('user')->findOrFail($id);

        return view('attendance-register.attendance-detail', compact('attendance'));
    }

    public function exportCsv(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $month = $request->query('month', now()->format('Y-m'));
        $date = \Carbon\Carbon::parse($month)->startOfMonth();

        $attendances = Attendance::where('user_id', $id)
            ->whereYear('date', $date->year)
            ->whereMonth('date', $date->month)
            ->get()
            ->keyBy('date');

        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename={$user->name}_{$month}_勤怠.csv",
        ];

        $callback = function() use ($attendances, $date) {
        $file = fopen('php://output', 'w');
        fputs($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($file, ['日付', '出勤', '退勤', '休憩時間', '合計勤務時間']);

        $daysInMonth = $date->daysInMonth;

        for ($i = 0; $i < $daysInMonth; $i++) {
            $currentDay = $date->copy()->addDays($i);
            $dateString = $currentDay->format('Y-m-d');
            $row = $attendances->get($dateString);

            fputcsv($file, [
                $currentDay->isoFormat('MM/DD(ddd)'),
                $row && $row->punch_in ? date('H:i', strtotime($row->punch_in)) : '',
                $row && $row->punch_out ? date('H:i', strtotime($row->punch_out)) : '',
                $row ? $row->total_rest_time : '',
                $row ? $row->actual_working_time : '',
            ]);
        }
        fclose($file);
    };

        return response()->stream($callback, 200, $headers);
    }
}
