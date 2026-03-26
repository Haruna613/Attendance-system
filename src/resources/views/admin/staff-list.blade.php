@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/staff-list.css') }}">
@endsection

@section('content')
<div class="main__inner">
    <h1 class="main__inner-title">スタッフ一覧</h1>

    <table class="staff-list-table">
        <thead>
            <tr class="staff-list-table__header">
                <th style="width: 33%;">名前</th>
                <th style="width: 33%;">メールアドレス</th>
                <th style="width: 33%;">月次勤怠</th>
            </tr>
        </thead>
        <tbody>
            @foreach($staff as $user)
            <tr class="staff-list-table__body">
                <td style="width: 33%;">{{ $user->name }}</td>
                <td style="width: 33%;">{{ $user->email }}</td>
                <td style="width: 33%;">
                    <a href="{{ route('admin.attendance.list', ['id' => $user->id]) }}" style="text-decoration: none; color: #000;">詳細</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection