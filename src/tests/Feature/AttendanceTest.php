<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;
use App\Models\StampCorrectionRequest;
use Carbon\Carbon;

class AttendanceTest extends TestCase
{
    public function test_current_date_elements_exist_with_clock_script()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/attendance');

        $response->assertStatus(200);

        $response->assertSee('id="current-date"', false);
        $response->assertSee('id="current-time"', false);

        $response->assertSee('function updateClock()', false);
        $response->assertSee('dayList = ["日", "月", "火", "水", "木", "金", "土"]', false);
    }

    use RefreshDatabase;

    public function test_status_is_off_work_when_no_attendance_exists()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/attendance');

        $response->assertStatus(200);
        $response->assertSeeText('勤務外');
    }

    public function test_status_is_working_when_punched_in()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::now()->format('Y-m-d'),
            'punch_in' => Carbon::now()->format('H:i:s'),
        ]);

        $response = $this->get('/attendance');

        $response->assertStatus(200);
        $response->assertSeeText('出勤中');
    }

    public function test_status_is_resting_when_taking_a_break()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::now()->format('Y-m-d'),
            'punch_in' => Carbon::now()->subHour()->format('H:i:s'),
        ]);

        Rest::create([
            'attendance_id' => $attendance->id,
            'start_time' => Carbon::now()->format('H:i:s'),
        ]);

        $response = $this->get('/attendance');

        $response->assertStatus(200);
        $response->assertSeeText('休憩中');
    }

    public function test_status_is_finished_when_punched_out()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::now()->format('Y-m-d'),
            'punch_in' => Carbon::now()->subHours(8)->format('H:i:s'),
            'punch_out' => Carbon::now()->format('H:i:s'),
        ]);

        $response = $this->get('/attendance');

        $response->assertStatus(200);
        $response->assertSeeText('退勤済');
    }

    public function test_work_start_button_functions_correctly()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/attendance');
        $response->assertStatus(200);

        $response->assertSee('出勤');

        $response = $this->post('/attendance/start');

        $response = $this->get('/attendance');
        $response->assertSee('出勤中');
    }

    public function test_cannot_punch_in_twice_a_day()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::now()->format('Y-m-d'),
            'punch_in' => '09:00:00',
            'punch_out' => '18:00:00',
        ]);

        $response = $this->get('/attendance');

        $response->assertDontSee('出勤');
    }

    public function test_punch_in_time_is_recorded_correctly_on_list_view()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $now = Carbon::create(2026, 2, 1, 9, 0, 0);
        Carbon::setTestNow($now);

        $this->post('/attendance/start');

        $response = $this->get('/attendance/list');
        $response->assertStatus(200);

        $response->assertSee('02/01(日)');
        $response->assertSee('09:00');

        Carbon::setTestNow();
    }

    public function test_rest_start_button_functions_correctly()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::now()->format('Y-m-d'),
            'punch_in' => '09:00:00',
        ]);

        $response = $this->get('/attendance');
        $response->assertSee('休憩入');

        $this->post('/attendance/rest-start');

        $response = $this->get('/attendance');
        $response->assertSee('休憩中');
    }

    public function test_can_take_rest_multiple_times()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::now()->format('Y-m-d'),
            'punch_in' => '09:00:00',
        ]);

        $this->post('/attendance/rest-start');
        $this->post('/attendance/rest-end');

        $response = $this->get('/attendance');
        $response->assertSee('休憩入');
    }

    public function test_rest_end_button_functions_correctly()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::now()->format('Y-m-d'),
            'punch_in' => '09:00:00',
        ]);

        $this->post('/attendance/rest-start');

        $response = $this->get('/attendance');
        $response->assertSee('休憩戻');

        $this->post('/attendance/rest-end');

        $response = $this->get('/attendance');
        $response->assertSee('出勤中');
    }

    public function test_can_end_rest_multiple_times()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::now()->format('Y-m-d'),
            'punch_in' => '09:00:00',
        ]);

        $this->post('/attendance/rest-start');
        $this->post('/attendance/rest-end');
        $this->post('/attendance/rest-start');

        $response = $this->get('/attendance');
        $response->assertSee('休憩戻');
    }

    public function test_rest_time_is_recorded_correctly_on_list_view()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $now = Carbon::create(2026, 2, 1, 9, 0, 0);
        Carbon::setTestNow($now);

        $this->post('/attendance/start');

        Carbon::setTestNow($now->copy()->addHours(1));
        $this->post('/attendance/rest-start');
        Carbon::setTestNow($now->copy()->addHours(1)->addMinutes(30));
        $this->post('/attendance/rest-end');

        $response = $this->get('/attendance/list');
        $response->assertStatus(200);

        $response->assertSee('02/01(日)');
        $response->assertSee('0:30');

        Carbon::setTestNow();
    }

    public function test_work_end_button_functions_correctly()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::now()->format('Y-m-d'),
            'punch_in' => '09:00:00',
        ]);

        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('退勤');

        $this->post('/attendance/end');

        $response = $this->get('/attendance');
        $response->assertSee('退勤済');
    }

    public function test_punch_out_time_is_recorded_correctly_on_list_view()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $now = Carbon::create(2026, 2, 1, 9, 0, 0);
        Carbon::setTestNow($now);

        $this->post('/attendance/start');

        Carbon::setTestNow($now->copy()->addHours(8));
        $this->post('/attendance/end');

        $response = $this->get('/attendance/list');
        $response->assertStatus(200);

        $response->assertSee('02/01(日)');
        $response->assertSee('17:00');

        Carbon::setTestNow();
    }

    public function test_user_can_see_their_own_attendance_list()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Attendance::create(['user_id' => $user->id, 'date' => now()->format('Y-m-01'), 'punch_in' => '09:00:00']);
        Attendance::create(['user_id' => $user->id, 'date' => now()->format('Y-m-02'), 'punch_in' => '10:00:00']);

        $response = $this->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSee('09:00');
        $response->assertSee('10:00');
    }

    public function test_attendance_list_displays_current_month_by_default()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $fakeNow = Carbon::create(2026, 2, 1);
        Carbon::setTestNow($fakeNow);

        $response = $this->get('/attendance/list');

        $response->assertSee('2026/02');

        Carbon::setTestNow();
    }

    public function test_attendance_list_navigates_to_previous_month()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Carbon::setTestNow(Carbon::create(2026, 2, 1));
        Attendance::create(['user_id' => $user->id, 'date' => '2026-01-15', 'punch_in' => '08:45:00']);

        $response = $this->get('/attendance/list?month=2026-01');

        $response->assertSee('2026/01');
        $response->assertSee('08:45');

        Carbon::setTestNow();
    }

    public function test_attendance_list_navigates_to_next_month()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Carbon::setTestNow(Carbon::create(2026, 2, 1));
        Attendance::create(['user_id' => $user->id, 'date' => '2026-03-05', 'punch_in' => '11:00:00']);

        $response = $this->get('/attendance/list?month=2026-03');

        $response->assertSee('2026/03');
        $response->assertSee('11:00');

        Carbon::setTestNow();
    }

    public function test_detail_link_redirects_to_correct_attendance_detail_page()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-03-01',
            'punch_in' => '09:00:00'
        ]);

        $response = $this->get('/attendance/list');

        $response->assertSee('/attendance/detail/' . $attendance->id, false);

        $detailResponse = $this->get('/attendance/detail/' . $attendance->id);
        $detailResponse->assertStatus(200);
    }

    public function test_attendance_detail_displays_user_name()
    {
        $user = User::factory()->create(['name' => 'テスト太郎']);
        $this->actingAs($user);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-02-01',
            'punch_in' => '09:00:00'
        ]);

        $response = $this->get("/attendance/detail/{$attendance->id}");

        $response->assertStatus(200);
        $response->assertSee('テスト太郎');
    }

    public function test_attendance_detail_displays_correct_date()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-02-01',
            'punch_in' => '09:00:00'
        ]);

        $response = $this->get("/attendance/detail/{$attendance->id}");

        $response->assertSee('2026年');
        $response->assertSee('2月');
        $response->assertSee('1日');
    }

    public function test_attendance_detail_displays_correct_punch_times()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-02-01',
            'punch_in' => '08:55:00',
            'punch_out' => '18:05:00'
        ]);

        $response = $this->get("/attendance/detail/{$attendance->id}");

        $response->assertSee('08:55');
        $response->assertSee('18:05');
    }

    public function test_attendance_detail_displays_correct_rest_times()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-02-01',
            'punch_in' => '09:00:00'
        ]);

        Rest::create([
            'attendance_id' => $attendance->id,
            'start_time' => '12:00:00',
            'end_time' => '13:00:00'
        ]);
        Rest::create([
            'attendance_id' => $attendance->id,
            'start_time' => '15:00:00',
            'end_time' => '15:15:00'
        ]);

        $response = $this->get("/attendance/detail/{$attendance->id}");

        $response->assertSee('12:00');
        $response->assertSee('13:00');
        $response->assertSee('15:00');
        $response->assertSee('15:15');
    }

    public function test_error_when_punch_in_after_punch_out()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $attendance = Attendance::create(['user_id' => $user->id, 'date' => '2026-02-01', 'punch_in' => '09:00']);

        $response = $this->patch("/attendance/update/{$attendance->id}", [
            'punch_in' => '18:00',
            'punch_out' => '09:00',
            'remarks' => '修正理由'
        ]);

        $response->assertSessionHasErrors();
        $this->get("/attendance/detail/{$attendance->id}")->assertSee('出勤時間もしくは退勤時間が不適切な値です');
    }

    public function test_error_when_rest_start_after_punch_out()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $attendance = Attendance::create(['user_id' => $user->id, 'date' => '2026-02-01', 'punch_in' => '09:00', 'punch_out' => '17:00']);

        $response = $this->patch("/attendance/update/{$attendance->id}", [
            'punch_in' => '09:00',
            'punch_out' => '17:00',
            'new_rests' => [['start' => '18:00', 'end' => '19:00']],
            'remarks' => '修正理由'
        ]);

        $response->assertSessionHasErrors();
        $this->get("/attendance/detail/{$attendance->id}")->assertSee('休憩時間が不適切な値です');
    }

    public function test_error_when_rest_end_after_punch_out()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $attendance = Attendance::create(['user_id' => $user->id, 'date' => '2026-02-01', 'punch_in' => '09:00', 'punch_out' => '17:00']);

        $response = $this->patch("/attendance/update/{$attendance->id}", [
            'punch_in' => '09:00',
            'punch_out' => '17:00',
            'new_rests' => [['start' => '16:00', 'end' => '18:00']],
            'remarks' => '修正理由'
        ]);

        $response->assertSessionHasErrors();

        $this->get("/attendance/detail/{$attendance->id}")->assertSee('休憩時間もしくは退勤時間が不適切な値です');
    }

    public function test_error_when_remarks_is_missing()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::create(['user_id' => $user->id, 'date' => '2026-02-01', 'punch_in' => '09:00']);

        $response = $this->patch("/attendance/update/{$attendance->id}", [
            'punch_in' => '09:00',
            'punch_out' => '18:00',
            'remarks' => ''
        ]);

        $this->get("/attendance/detail/{$attendance->id}")->assertSee('備考を記入してください');
    }

    public function test_correction_request_is_visible_to_admin()
    {
        $user = User::factory()->create();
        $admin = User::factory()->create(['role' => 1]);

        $this->actingAs($user);
        $attendance = Attendance::create(['user_id' => $user->id, 'date' => '2026-02-01', 'punch_in' => '09:00']);

        $this->patch("/attendance/update/{$attendance->id}", [
            'punch_in' => '08:00',
            'punch_out' => '17:00',
            'remarks' => '時間修正'
        ]);

        $this->actingAs($admin);
        $response = $this->get('/stamp_correction_request/list');
        $response->assertSee($user->name);
        $response->assertSee('時間修正');
    }

    public function test_user_can_see_their_own_pending_requests()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::create(['user_id' => $user->id, 'date' => '2026-02-01', 'punch_in' => '09:00']);

        $this->patch("/attendance/update/{$attendance->id}", ['punch_in' => '08:00', 'punch_out' => '17:00', 'remarks' => '申請1']);

        $response = $this->get('/stamp_correction_request/list?tab=pending');
        $response->assertSee('申請1');
    }

    public function test_user_can_see_approved_requests()
    {
        $user = User::factory()->create();

        $admin = User::factory()->create(['role' => 1]);
        $this->actingAs($user);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-02-01',
            'punch_in' => '09:00',
            'punch_out' => '18:00',
        ]);

        $this->patch("/attendance/update/{$attendance->id}", [
            'punch_in' => '08:30',
            'punch_out' => '17:30',
            'remarks' => '承認された理由'
        ]);

        $attendance->refresh();
        $attendance->update([
            'status' => 0,
            'approved_at' => now()
        ]);

        $this->actingAs($user);
        $response = $this->get('/stamp_correction_request/list?tab=approved');

        $response->assertStatus(200);
        $response->assertSee('承認された理由');
        $response->assertDontSee('申請はありません');
    }

    public function test_request_list_detail_link_works()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-02-01',
            'punch_in' => '09:00',
            'status' => 1
        ]);

        $response = $this->get('/stamp_correction_request/list');
        $response->assertSee("/attendance/detail/{$attendance->id}", false);
    }
}