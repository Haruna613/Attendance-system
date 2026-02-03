<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;
use Carbon\Carbon;

class AdminAttendanceListTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;
    protected $targetAttendance;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminUser = User::factory()->create(['role' => 0]);

        $user = User::factory()->create(['name' => 'テスト太郎']);

        $this->targetAttendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::now()->format('Y-m-d'),
            'punch_in' => '09:00:00',
            'punch_out' => '18:00:00',
        ]);

        Rest::create([
            'attendance_id' => $this->targetAttendance->id,
            'start_time' => '12:00:00',
            'end_time' => '13:00:00',
        ]);
    }

    public function test_admin_can_see_all_users_attendance_of_the_day()
    {
        $response = $this->actingAs($this->adminUser)->get('/admin/attendance/list');
        $response->assertStatus(200);
        $response->assertSee('テスト太郎');
    }

    public function test_admin_attendance_list_displays_current_date_by_default()
    {
        $today = Carbon::now()->format('Y/m/d');
        $response = $this->actingAs($this->adminUser)->get('/admin/attendance/list');
        $response->assertSee($today);
    }

    public function test_admin_can_navigate_to_previous_day()
    {
        $yesterday = Carbon::yesterday();
        $response = $this->actingAs($this->adminUser)
                         ->get("/admin/attendance/list?date=" . $yesterday->format('Y-m-d'));
        $response->assertSee($yesterday->format('Y年n月j日'));
    }

    public function test_admin_can_navigate_to_next_day()
    {
        $tomorrow = Carbon::tomorrow();
        $response = $this->actingAs($this->adminUser)
                         ->get("/admin/attendance/list?date=" . $tomorrow->format('Y-m-d'));
        $response->assertSee($tomorrow->format('Y年n月j日'));
    }

    public function test_admin_can_see_correct_attendance_details()
    {
        $response = $this->actingAs($this->adminUser)
                         ->get("/attendance/detail/{$this->targetAttendance->id}");
        $response->assertStatus(200);
        $response->assertSee('テスト太郎');
    }

    public function test_error_when_admin_sets_punch_in_after_punch_out()
    {
        $response = $this->actingAs($this->adminUser)
                         ->patch("/attendance/update/{$this->targetAttendance->id}", [
                             'punch_in' => '19:00',
                             'punch_out' => '18:00',
                             'remarks' => '修正',
                         ]);
        $response->assertSessionHasErrors();
    }

    public function test_error_when_admin_sets_rest_start_after_punch_out()
    {
        $response = $this->actingAs($this->adminUser)
                         ->patch("/attendance/update/{$this->targetAttendance->id}", [
                             'punch_in' => '09:00',
                             'punch_out' => '18:00',
                             'rests' => [['start' => '19:00', 'end' => '20:00']],
                             'remarks' => '修正',
                         ]);
        $response->assertSessionHasErrors();
    }

    public function test_error_when_admin_sets_rest_end_after_punch_out()
    {
        $response = $this->actingAs($this->adminUser)
                         ->patch("/attendance/update/{$this->targetAttendance->id}", [
                             'punch_in' => '09:00',
                             'punch_out' => '18:00',
                             'rests' => [['start' => '12:00', 'end' => '19:00']],
                             'remarks' => '修正',
                         ]);
        $response->assertSessionHasErrors();
    }

    public function test_error_when_admin_updates_without_remarks()
    {
        $response = $this->actingAs($this->adminUser)
                         ->patch("/attendance/update/{$this->targetAttendance->id}", [
                             'punch_in' => '09:00',
                             'punch_out' => '18:00',
                             'remarks' => '',
                         ]);
        $response->assertSessionHasErrors(['remarks']);
    }
}