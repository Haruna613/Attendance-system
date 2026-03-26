@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance-list.css') }}">
@endsection

@section('content')
<div class="main__inner">
    <h1 class="main__inner-title">{{ $user->name }}さんの勤怠一覧</h1>

    <nav class="attendance-list__header">
        <ul class="attendance-list__header-content">
            <li class="attendance-list__header-content-item">
                <a class="attendance-list__nav-link--prev" href="{{ route('admin.attendance.list', ['id' => $user->id, 'month' => $prevMonth]) }}">←前月</a>
            </li>
            <li class="attendance-list__header-content-item">
                <div class="attendance-list__current-month">
                    <img class="calendar-image" src="{{ asset('images/カレンダー.png') }}" alt="ロゴ">
                    <span class="attendance-list__current-date-text">{{ $currentDate->format('Y/m') }}</span>
                </div>
            </li>
            <li class="attendance-list__header-content-item">
                <a class="attendance-list__nav-link--next" href="{{ route('admin.attendance.list', ['id' => $user->id, 'month' => $nextMonth]) }}">翌月→</a>
            </li>
        </ul>
    </nav>

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
                <td style="padding: 10px 5px;">{{ \Carbon\Carbon::parse($day->date)->isoFormat('MM/DD(ddd)') }}</td>
                <td style="padding: 10px 5px;">{{ $day->punch_in ? date('H:i', strtotime($day->punch_in)) : '' }}</td>
                <td style="padding: 10px 5px;">{{ $day->punch_out ? date('H:i', strtotime($day->punch_out)) : '' }}</td>
                <td style="padding: 10px 5px;">{{ $day->total_rest_time }}</td>
                <td style="padding: 10px 5px;">{{ $day->actual_working_time }}</td>
                <td style="padding: 10px 5px;">
                    @if($day->id)
                        <a class="attendance-list__detail-link" href="{{ route('admin.attendance.detail', ['id' => $day->id]) }}">
                            詳細
                        </a>
                    @else
                        <span class="disabled-link" style="color: #000;">詳細</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="csv-export">
        <a href="{{ route('admin.attendance.csv', ['id' => $user->id, 'month' => $currentDate->format('Y-m')]) }}" class="csv-button">
            CSV出力
        </a>
    </div>
</div>
@endsection