<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AdminUserControlTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;
    protected $targetUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'name' => '管理者ユーザー',
            'role' => 1,
        ]);

        $this->targetUser = User::factory()->create([
            'name' => 'スタッフA',
            'email' => 'staff-a@example.com',
            'role' => 0,
        ]);
    }

    public function test_admin_can_see_all_staff_list()
    {
        $response = $this->actingAs($this->adminUser)->get('/admin/staff/list');

        $response->assertStatus(200);

        $response->assertSee('スタッフA');
        $response->assertSee('staff-a@example.com');
    }

    public function test_admin_can_navigate_staff_attendance_to_previous_month()
    {
        $targetMonth = '2026-01';

        $response = $this->actingAs($this->adminUser)
                         ->get("/admin/attendance/staff/{$this->targetUser->id}?month={$targetMonth}");

        $response->assertStatus(200);
        $response->assertSee('2026/01');
    }

    public function test_admin_can_navigate_staff_attendance_to_next_month()
    {
        $targetMonth = '2026-03';

        $response = $this->actingAs($this->adminUser)
                         ->get("/admin/attendance/staff/{$this->targetUser->id}?month={$targetMonth}");

        $response->assertStatus(200);
        $response->assertSee('2026/03');
    }

    public function test_admin_can_see_correct_detail_link()
    {
        $attendance = Attendance::factory()->create([
            'user_id' => $this->targetUser->id,
            'date' => now()->format('Y-m-d'),
            'punch_in' => '09:00:00',
            'punch_out' => '18:00:00',
        ]);

        $response = $this->actingAs($this->adminUser)
                         ->get("/admin/attendance/staff/{$this->targetUser->id}");

        $response->assertStatus(200);
        $response->assertSee("/admin/attendance/{$attendance->id}");
    }

    public function test_admin_can_see_all_pending_requests()
    {
        $pendingAttendance = Attendance::factory()->create([
            'user_id' => $this->targetUser->id,
            'status' => 1,
            'remarks' => '修正願い：打刻ミス'
        ]);

        $approvedAttendance = Attendance::factory()->create([
            'user_id' => $this->targetUser->id,
            'status' => 0
        ]);

        $response = $this->actingAs($this->adminUser)
                         ->get('/stamp_correction_request/list?tab=pending');

        $response->assertStatus(200);
        $response->assertSee($pendingAttendance->user->name);
        $response->assertSee('修正願い：打刻ミス');
        $response->assertDontSee($approvedAttendance->remarks);
    }

    public function test_admin_can_see_all_approved_requests()
    {
        $approvedAttendance = Attendance::factory()->create([
            'user_id' => $this->targetUser->id,
            'status' => 0,
            'date' => '2026-02-02',
            'applied_at' => now(),
            'remarks' => '承認済みデータ'
        ]);

        $response = $this->actingAs($this->adminUser)
                         ->get('/stamp_correction_request/list?tab=approved');

        $response->assertStatus(200);
        $response->assertSee($this->targetUser->name);
        $response->assertSee('承認済みデータ');
    }

    public function test_admin_can_see_request_detail()
    {
        $attendance = Attendance::factory()->create([
            'user_id' => $this->targetUser->id,
            'status' => 1,
            'remarks' => '詳細確認テスト',
            'punch_in' => '09:00:00',
            'punch_out' => '18:00:00',
        ]);

        $response = $this->actingAs($this->adminUser)
                         ->get("/stamp_correction_request/approve/{$attendance->id}");

        $response->assertStatus(200);
        $response->assertSee($this->targetUser->name);
        $response->assertSee('詳細確認テスト');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    public function test_admin_can_approve_request()
    {
        $attendance = Attendance::factory()->create([
            'user_id' => $this->targetUser->id,
            'status' => 1
        ]);

        $response = $this->actingAs($this->adminUser)
                         ->post("/stamp_correction_request/approve/{$attendance->id}");

        $this->assertEquals(0, $attendance->fresh()->status);

        $response->assertRedirect(route('admin.attendance.list', ['id' => $attendance->user_id]));
    }
}