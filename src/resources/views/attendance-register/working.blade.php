@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/working.css') }}">
@endsection

@section('content')
<div class="main__inner">
    <div class="attendance-registration__item--status-section">
        <span class="attendance-status">出勤中</span>
    </div>
    <div class="attendance-registration__item--clock-section">
        <p id="current-date" class="date-text"></p>
        <p id="current-time" class="time-text"></p>
    </div>
    <div class="attendance-registration__item--button-section">
        <form action="{{ route('attendance.end') }}" method="POST" style="display:inline;">
            @csrf
            <button type="submit" class="work-end__button">退勤</button>
        </form>
        <form action="{{ route('attendance.rest.start') }}" method="POST" style="display:inline;">
            @csrf
            <button type="submit" class="rest-start__button">休憩入</button>
        </form>
    </div>
</div>
<script>
    function updateClock() {
        const now = new Date();
        const year = now.getFullYear();
        const month = now.getMonth() + 1;
        const date = now.getDate();
        const dayList = ["日", "月", "火", "水", "木", "金", "土"];
        const day = dayList[now.getDay()];
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        document.getElementById('current-date').textContent = `${year}年${month}月${date}日(${day})`;
        document.getElementById('current-time').textContent = `${hours}:${minutes}`;
    }
    setInterval(updateClock, 1000);
    updateClock();
</script>
@endsection