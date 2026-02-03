@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance-detail.css') }}">
@endsection

@section('content')
<div class="main__inner">
    <h1 class="main__inner__title">勤怠詳細</h1>

    <form action="{{ route('admin.attendance.approve', $attendance->id) }}" method="POST">
        @csrf

        <table class="attendance-detail__table">
            <tr class="attendance-detail__table-row">
                <th class="attendance-detail__table-row__title">名前</th>
                <td class="attendance-detail__table-row__value">{{ $attendance->user->name }}</td>
            </tr>
            <tr class="attendance-detail__table-row">
                <th class="attendance-detail__table-row__title">日付</th>
                <td class="attendance-detail__table-row__value-year">
                    {{ \Carbon\Carbon::parse($attendance->date)->isoFormat('YYYY年') }}
                </td>
                <td class="attendance-detail__table-row__value-date">
                    {{ \Carbon\Carbon::parse($attendance->date)->isoFormat('M月D日') }}
                </td>
            </tr>
            <tr class="attendance-detail__table-row">
                <th class="attendance-detail__table-row__title">出勤・退勤</th>
                <td class="attendance-detail__table-row__value">
                    <div class="time-group">
                        <span class="time-group--item">
                            <p class="display-value">{{ date('H:i', strtotime($attendance->punch_in)) }}</p>
                        </span>
                        <span class="time-separator">〜</span>
                        <span class="time-group--item">
                            <p class="display-value">{{ $attendance->punch_out ? date('H:i', strtotime($attendance->punch_out)) : '' }}</p>
                        </span>
                    </div>
                </td>
            </tr>

            @foreach($attendance->rests as $index => $rest)
            <tr class="attendance-detail__table-row">
                <th class="attendance-detail__table-row__title">休憩{{ $index > 0 ? $index + 1 : '' }}</th>
                <td class="attendance-detail__table-row__value">
                    <div class="time-group">
                        <span class="time-group--item">
                            <p class="display-value">{{ date('H:i', strtotime($rest->start_time)) }}</p>
                            <p class="time-separator">〜</p>
                            <p class="display-value">{{ $rest->end_time ? date('H:i', strtotime($rest->end_time)) : '' }}</p>
                        </span>
                    </div>
                </td>
            </tr>
            @endforeach

            <tr class="attendance-detail__table-row">
                <th class="attendance-detail__table-row__title">備考</th>
                <td class="attendance-detail__table-row__value">
                    <p class="display-value">{{ $attendance->remarks }}</p>
                </td>
            </tr>
        </table>

        <div class="attendance-detail__form-actions">
            @if($attendance->status == 0)
                <p class="approved-message">
                    承認済み
                </p>
            @else
                <button type="submit" class="submit-button">
                    承認
                </button>
            @endif
        </div>
    </form>
</div>
@endsection