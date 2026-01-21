@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance-detail.css') }}">
@endsection

@section('content')
<div class="main__inner">
    <h1 class="main__inner__title">勤怠詳細</h1>

    @php
        $isNew = is_null($attendance->id);
        $isPending = $attendance->status == 1;
        $action = $isNew ? route('attendance.store') : route('attendance.update', $attendance->id);
    @endphp

    <form action="{{ $action }}" method="POST">
        @csrf
        @if(!$isNew)
            @method('PATCH')
        @endif

        @if($isNew)
        <input type="hidden" name="date" value="{{ $attendance->date }}">
        @endif
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
                            @if($isPending)
                            <p class="display-value">{{ date('H:i', strtotime($attendance->punch_in)) }}</p>
                            @else
                            <input class="time-input" type="time" name="punch_in" value="{{ $attendance->punch_in ? date('H:i', strtotime($attendance->punch_in)) : '' }}">
                            @endif
                        </span>
                        <span class="time-separator">〜</span>
                        <span class="time-group--item">
                            @if($isPending)
                            <p class="display-value">{{ $attendance->punch_out ? date('H:i', strtotime($attendance->punch_out)) : '' }}</p>
                            @else
                            <input class="time-input" type="time" name="punch_out" value="{{ $attendance->punch_out ? date('H:i', strtotime($attendance->punch_out)) : '' }}">
                            @endif
                        </span>
                    </div>
                    @error('punch_in')
                        <p class="error-message">{{ $message }}</p>
                    @enderror
                    @error('punch_out')
                        <p class="error-message">{{ $message }}</p>
                    @enderror
                </td>
            </tr>

            @foreach($attendance->rests as $index => $rest)
            <tr class="attendance-detail__table-row">
                <th class="attendance-detail__table-row__title">休憩{{ $index > 0 ? $index + 1 : '' }}</th>
                <td class="attendance-detail__table-row__value">
                    <div class="time-group">
                        @if($isPending)
                        <span class="time-group--item">
                            <p class="display-value">{{ date('H:i', strtotime($rest->start_time)) }}</p>
                            <p class="time-separator">〜</p>
                            <p class="display-value">{{ $rest->end_time ? date('H:i', strtotime($rest->end_time)) : '' }}</p>
                        </span>
                        @else
                        <span class="time-group--item">
                            <input class="time-input" type="time" name="rests[{{ $rest->id }}][start]" value="{{ date('H:i', strtotime($rest->start_time)) }}">
                        </span>
                        <span class="time-separator">〜</span>
                        <span class="time-group--item">
                            <input class="time-input" type="time" name="rests[{{ $rest->id }}][end]" value="{{ $rest->end_time ? date('H:i', strtotime($rest->end_time)) : '' }}">
                        </span>
                    </div>
                    @error('rests')
                        <p class="error-message">{{ $message }}</p>
                    @enderror
                    @endif
                </td>
            </tr>
            @endforeach

            @if(!$isPending)
            @php
                $existingCount = $isNew ? 0 : $attendance->rests->count();
                $freeSlots = $isNew ? 2 : 1;
            @endphp
            @for ($i = 0; $i < $freeSlots; $i++)
            <tr class="attendance-detail__table-row">
                <th class="attendance-detail__table-row__title">休憩{{ ($existingCount + $i) > 0 ? ($existingCount + $i + 1) : '' }}</th>
                <td class="attendance-detail__table-row__value">
                    <div class="time-group">
                        <span class="time-group--item">
                            <input class="time-input" type="time" name="new_rests[{{ $i }}][start]">
                        </span>
                        <span class="time-separator">〜</span>
                        <span class="time-group--item">
                            <input class="time-input" type="time" name="new_rests[{{ $i }}][end]">
                        </span>
                    </div>
                    @error('rests')
                        <p class="error-message">{{ $message }}</p>
                    @enderror
                </td>
            </tr>
            @endfor
            @endif
            <tr class="attendance-detail__table-row">
                <th class="attendance-detail__table-row__title">備考</th>
                <td class="attendance-detail__table-row__value">
                    <div>
                        @if($isPending)
                        <p class="display-value">{{ $attendance->remarks }}</p>
                        @else
                        <textarea class="description" name="remarks" rows="4">{{ $attendance->remarks }}</textarea>
                    </div>
                    @error('remarks')
                        <p class="error-message">{{ $message }}</p>
                    @enderror
                    @endif
                </td>
            </tr>
        </table>

        <div class="attendance-detail__form-actions">
            @if ($isPending)
            <p class="info-message">
                *承認待ちのため修正はできません。
            </p>
            @else
            <button type="submit" class="submit-button">
                修正
            </button>
            @endif
        </div>
    </form>
</div>

<script>
    document.getElementById('add-rest').addEventListener('click', function() {
        const container = document.getElementById('rest-container');
        const count = container.getElementsByClassName('rest-item').length + 1;
        const div = document.createElement('div');
        div.className = 'rest-item';
        div.style.marginBottom = '10px';
        div.innerHTML = `
            <span>休憩${count}</span>
            <input type="time" name="new_rests[][start]" required>
            <span>〜</span>
            <input type="time" name="new_rests[][end]" required>
        `;
        container.appendChild(div);
    });
</script>
@endsection