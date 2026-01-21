@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/correction-list.css') }}">
@endsection

@section('content')
<div class="main__inner">
    <h1 class="main__inner__title">申請一覧</h1>

    <div class="tabs">
        <a class="tab-link" href="{{ route('attendance.correction.list', ['tab' => 'pending']) }}" style="color: {{ $tab === 'pending' ? '#000' : '#888' }};">
            承認待ち
        </a>
        <a class="tab-link" href="{{ route('attendance.correction.list', ['tab' => 'approved']) }}" style="color: {{ $tab === 'approved' ? '#000' : '#888' }};">
            承認済み
        </a>
    </div>

    <table class="correction-list-table">
        <thead class="correction-list-table-head">
            <tr>
                <th style="width: 15%; padding: 10px 5%;">状態</th>
                <th style="width: 15%; padding: 10px 0;">名前</th>
                <th style="width: 20%; padding: 10px 0;">対象日時</th>
                <th style="width: 15%; padding: 10px 0;">申請理由</th>
                <th style="width: 20%; padding: 10px 0;">申請日時</th>
                <th style="width: 10%; padding: 10px 0;">詳細</th>
            </tr>
        </thead>
        <tbody class="correction-list-table-body">
            @forelse($requests as $attendance)
            <tr class="correction-list-table-body-item">
                <td style="width: 15%; padding: 10px 5%;">
                    {{ $attendance->status == 1 ? '承認待ち' : '承認済み' }}
                </td>
                <td style="width: 15%; padding: 10px 0;">{{ $attendance->user->name }}</td>
                <td style="width: 20%; padding: 10px 0;">
                    {{ \Carbon\Carbon::parse($attendance->date)->format('Y/m/d') }}
                </td>
                <td style="width: 15%; padding: 10px 0;">{{ Str::limit($attendance->remarks, 20) }}</td>
                <td style="width: 20%; padding: 10px 0;">
                    {{ $attendance->updated_at->format('Y/m/d') }}
                </td>
                <td style="width: 10%; padding: 10px 0;">
                    <a href="{{ route('attendance.detail', $attendance->id) }}" style="color: #000; text-decoration: none;">詳細</a>
                </td>
            </tr>
            @empty
            <tr class="correction-list-table-body-item">
                <td colspan="6" style="padding: 20px; text-align: center; color: #888;">申請はありません</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection