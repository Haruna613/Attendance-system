@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance-list.css') }}">
@endsection

@section('content')
<div class="main__inner">
    <h1 class="main__inner__title">勤怠一覧</h1>
    <div class="attendance-list__header">
        <a class="attendance-list__nav-link--prev" href="{{ route('attendance.list', ['month' => $prevMonth]) }}">←前月</a>
        <div class="attendance-list__current-month">
            <img class="calendar-image" src="{{ asset('images/カレンダー.png') }}" alt="ロゴ">
            <h2>{{ $currentDate->format('Y/m') }}</h2>
        </div>
        <a class="attendance-list__nav-link--next" href="{{ route('attendance.list', ['month' => $nextMonth]) }}">翌月→</a>
    </div>
    <table class="attendance-list__table">
        <thead class="attendance-list__table-head">
            <tr>
                <th style="padding: 10px 5px;">日付</th>
                <th style="padding: 10px 5px;">出勤</th>
                <th style="padding: 10px 5px;">退勤</th>
                <th style="padding: 10px 5px;">休憩</th>
                <th style="padding: 10px 5px;">合計</th>
                <th style="padding: 10px 5px;">詳細</th>
            </tr>
        </thead>
        <tbody class="attendance-list__table-body">
            @foreach ($calendar as $day)
            <tr class="attendance-list__table-body-item">
                <td style="padding: 10px 5px;">{{ Carbon\Carbon::parse($day->date)->isoFormat('MM/DD(ddd)') }}</td>
                <td style="padding: 10px 5px;">{{ $day->punch_in ? date('H:i', strtotime($day->punch_in)) : '' }}</td>
                <td style="padding: 10px 5px;">{{ $day->punch_out ? date('H:i', strtotime($day->punch_out)) : '' }}</td>
                <td style="padding: 10px 5px;">{{ $day->total_rest_time }}</td>
                <td style="padding: 10px 5px;">{{ $day->actual_working_time }}</td>
                <td style="padding: 10px 5px;">
                    <a class="attendance-list__detail-link" href="{{ route('attendance.detail', $day->id ?? $day->date) }}">詳細</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection