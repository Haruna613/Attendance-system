@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance-list.css') }}">
@endsection

@section('content')
<div class="main__inner">
    <h1 class="main__inner-title">{{ \Carbon\Carbon::parse($date)->isoFormat('YYYY年M月D日') }}の勤怠</h1>
    @php
        $current = \Carbon\Carbon::parse($date);
        $prevDay = $current->copy()->subDay()->format('Y-m-d');
        $nextDay = $current->copy()->addDay()->format('Y-m-d');
    @endphp
    <div class="attendance-list__header">
        <a class="attendance-list__nav-link--prev" href="{{ route('admin.attendance.all', ['date' => $prevDay]) }}">←前日</a>
        <div class="attendance-list__current-date">
            <img class="calendar-image" src="{{ asset('images/カレンダー.png') }}" alt="ロゴ">
            <h2>{{ $current->format('Y/m/d') }}</h2>
        </div>
        <a class="attendance-list__nav-link--next" href="{{ route('admin.attendance.all', ['date' => $nextDay]) }}">翌日→</a>
    </div>
    <table class="attendance-list__table">
        <thead class="attendance-list__table-head">
            <tr>
                <th style="padding: 10px 5px;">名前</th>
                <th style="padding: 10px 5px;">出勤</th>
                <th style="padding: 10px 5px;">退勤</th>
                <th style="padding: 10px 5px;">休憩</th>
                <th style="padding: 10px 5px;">合計</th>
                <th style="padding: 10px 5px;">詳細</th>
            </tr>
        </thead>
        <tbody class="attendance-list__table-body">
            @foreach($attendances as $attendance)
            <tr class="attendance-list__table-body-item">
                <td style="padding: 10px 5px;">{{ $attendance->user->name }}</td>
                <td style="padding: 10px 5px;">{{ date('H:i', strtotime($attendance->punch_in)) }}</td>
                <td style="padding: 10px 5px;">{{ $attendance->punch_out ? date('H:i', strtotime($attendance->punch_out)) : '-' }}</td>
                <td style="padding: 10px 5px;">{{ $attendance->total_rest_time }}</td>
                <td style="padding: 10px 5px;">{{ $attendance->actual_working_time }}</td>
                <td style="padding: 10px 5px;">
                    <a class="attendance-list__detail-link" href="{{ route('attendance.detail', $attendance->id ?? $attendance->date) }}">詳細</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection