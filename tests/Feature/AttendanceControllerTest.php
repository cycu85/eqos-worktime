<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\TaskWorkLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    }

    public function test_guests_cannot_access_attendance(): void
    {
        $response = $this->get(route('attendance.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_pracownik_cannot_access_attendance(): void
    {
        $pracownik = User::factory()->create(['role' => 'pracownik', 'is_active' => true]);
        $response = $this->actingAs($pracownik)->get(route('attendance.index'));
        $response->assertForbidden();
    }

    public function test_kierownik_can_access_attendance(): void
    {
        $kierownik = User::factory()->create(['role' => 'kierownik', 'is_active' => true]);
        $response = $this->actingAs($kierownik)->get(route('attendance.index'));
        $response->assertOk();
    }

    public function test_lider_cannot_access_attendance(): void
    {
        $lider = User::factory()->create(['role' => 'lider', 'is_active' => true]);
        $response = $this->actingAs($lider)->get(route('attendance.index'));
        $response->assertForbidden();
    }

    public function test_admin_can_access_attendance(): void
    {
        $response = $this->actingAs($this->admin)->get(route('attendance.index'));
        $response->assertOk();
        $response->assertViewIs('attendance.index');
    }

    public function test_leader_appears_in_attendance_when_work_log_completed(): void
    {
        $leader = User::factory()->create(['name' => 'Jan Kowalski', 'role' => 'lider', 'is_active' => true]);
        $task = Task::factory()->create(['leader_id' => $leader->id, 'team' => null]);
        TaskWorkLog::factory()->create([
            'task_id'   => $task->id,
            'work_date' => '2026-03-15',
            'status'    => 'completed',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('attendance.index', ['date_from' => '2026-03-01', 'date_to' => '2026-03-31']));

        $response->assertOk();
        $response->assertSee('Jan Kowalski');
        $response->assertSee('15.03.2026');
    }

    public function test_team_member_appears_in_attendance(): void
    {
        $leader = User::factory()->create(['name' => 'Anna Nowak', 'role' => 'lider', 'is_active' => true]);
        $member = User::factory()->create(['name' => 'Piotr Wiśniewski', 'role' => 'pracownik', 'is_active' => true]);
        $task = Task::factory()->create([
            'leader_id' => $leader->id,
            'team'      => 'Piotr Wiśniewski',
        ]);
        TaskWorkLog::factory()->create([
            'task_id'   => $task->id,
            'work_date' => '2026-03-20',
            'status'    => 'in_progress',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('attendance.index', ['date_from' => '2026-03-01', 'date_to' => '2026-03-31']));

        $response->assertOk();
        $response->assertSee('Piotr Wiśniewski');
    }

    public function test_planned_work_log_does_not_create_attendance(): void
    {
        $leader = User::factory()->create(['name' => 'Marek Zielony', 'role' => 'lider', 'is_active' => true]);
        $task = Task::factory()->create(['leader_id' => $leader->id, 'team' => null]);
        TaskWorkLog::factory()->create([
            'task_id'   => $task->id,
            'work_date' => '2026-03-10',
            'status'    => 'planned',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('attendance.index', ['date_from' => '2026-03-01', 'date_to' => '2026-03-31']));

        $response->assertOk();
        $response->assertDontSee('Marek Zielony');
    }

    public function test_duplicate_attendance_deduplicated(): void
    {
        $leader = User::factory()->create(['name' => 'Tomasz Lis', 'role' => 'lider', 'is_active' => true]);
        $task1 = Task::factory()->create(['leader_id' => $leader->id, 'team' => null]);
        $task2 = Task::factory()->create(['leader_id' => $leader->id, 'team' => null]);
        TaskWorkLog::factory()->create(['task_id' => $task1->id, 'work_date' => '2026-03-05', 'status' => 'completed']);
        TaskWorkLog::factory()->create(['task_id' => $task2->id, 'work_date' => '2026-03-05', 'status' => 'completed']);

        $response = $this->actingAs($this->admin)
            ->get(route('attendance.index', ['date_from' => '2026-03-01', 'date_to' => '2026-03-31']));

        $response->assertOk();
        // Powinien pojawić się dokładnie raz - liczymy wystąpienia
        $content = $response->getContent();
        $this->assertEquals(1, substr_count($content, 'Tomasz Lis'));
    }

    public function test_filter_by_user_id(): void
    {
        $leader1 = User::factory()->create(['name' => 'Adam Pierwszy', 'role' => 'lider', 'is_active' => true]);
        $leader2 = User::factory()->create(['name' => 'Ewa Druga', 'role' => 'lider', 'is_active' => true]);
        $task1 = Task::factory()->create(['leader_id' => $leader1->id, 'team' => null]);
        $task2 = Task::factory()->create(['leader_id' => $leader2->id, 'team' => null]);
        TaskWorkLog::factory()->create(['task_id' => $task1->id, 'work_date' => '2026-03-12', 'status' => 'completed']);
        TaskWorkLog::factory()->create(['task_id' => $task2->id, 'work_date' => '2026-03-12', 'status' => 'completed']);

        $response = $this->actingAs($this->admin)
            ->get(route('attendance.index', [
                'date_from' => '2026-03-01',
                'date_to'   => '2026-03-31',
                'user_id'   => $leader1->id,
            ]));

        $response->assertOk();
        $response->assertSee('Adam Pierwszy');
        $response->assertDontSee('Ewa Druga');
    }
}
