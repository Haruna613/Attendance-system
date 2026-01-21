@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/after-work.css') }}">
@endsection

@section('content')
<div class="main__inner">
    <div class="attendance-registration__item--status-section">
        <span class="attendance-status">退勤済</span>
    </div>
    <div class="attendance-registration__item--clock-section">
        <p id="current-date" class="date-text"></p>
        <p id="current-time" class="time-text"></p>
    </div>
    <div class="attendance-registration__item--message-section">
        <p class="message">お疲れ様でした。</p>
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