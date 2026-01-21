<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Rest;
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

    public function edit($idOrDate)
    {
        if (is_numeric($idOrDate)) {
            $attendance = Attendance::with(['user', 'rests'])->findOrFail($idOrDate);
        } else {
            $attendance = new Attendance([
                'date' => $idOrDate,
                'user_id' => Auth::id(),
                'status' => 0
            ]);
            $attendance->setRelation('rests', collect());
            $attendance->setRelation('user', Auth::user());
        }

        return view('attendance-register/attendance-detail', compact('attendance'));
    }

    public function update(AttendanceUpdateRequest $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        $attendance->update([
            'punch_in' => $request->punch_in,
            'punch_out' => $request->punch_out,
            'remarks' => $request->remarks,
            'status' => 1,
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

        return redirect()->route('attendance.list')->with('success', '修正申請を送信しました。管理者の承認をお待ちください。');
    }

    public function store(AttendanceUpdateRequest $request)
    {
        $attendance = Attendance::create([
            'user_id' => Auth::id(),
            'date' => $request->date,
            'punch_in' => $request->punch_in,
            'punch_out' => $request->punch_out,
            'remarks' => $request->remarks,
            'status' => 1,
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

        return redirect()->route('attendance.list')->with('success', '勤怠登録の申請を出しました。');
    }

    public function correctionList(Request $request)
    {
        $tab = $request->query('tab', 'pending');
        $status = ($tab === 'approved') ? 2 : 1;
        $requests = Attendance::where('status', $status)
            ->with('user')
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('attendance-register.correction-list', compact('requests', 'tab'));
    }
}
