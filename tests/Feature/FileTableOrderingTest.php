<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\FileRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FileTableOrderingTest extends TestCase
{
    use RefreshDatabase;

    public function test_prioritize_urgent_and_recent_scope_orders_unactioned_urgent_first_then_recent()
    {
        $dept = Department::create(['name' => 'Records', 'code' => 'REC', 'is_active' => true]);
        $user = User::factory()->create([
            'department_id' => $dept->id,
            'role' => 'admin',
            'is_active' => true,
        ]);

        // File 1: Old normal file
        $oldNormal = FileRecord::create([
            'file_name' => 'Old Normal File',
            'file_number' => 'REC/001',
            'department_id' => $dept->id,
            'current_department_id' => $dept->id,
            'current_user_id' => $user->id,
            'created_by' => $user->id,
            'status' => 'active',
            'is_urgent' => false,
        ]);
        $oldNormal->timestamps = false;
        $oldNormal->created_at = now()->subDays(5);
        $oldNormal->save();

        // File 2: New normal file
        $newNormal = FileRecord::create([
            'file_name' => 'New Normal File',
            'file_number' => 'REC/002',
            'department_id' => $dept->id,
            'current_department_id' => $dept->id,
            'current_user_id' => $user->id,
            'created_by' => $user->id,
            'status' => 'active',
            'is_urgent' => false,
        ]);
        $newNormal->timestamps = false;
        $newNormal->created_at = now()->subDay();
        $newNormal->save();

        // File 3: Urgent file that was already actioned/completed
        $urgentCompleted = FileRecord::create([
            'file_name' => 'Urgent Completed File',
            'file_number' => 'REC/003',
            'department_id' => $dept->id,
            'current_department_id' => $dept->id,
            'current_user_id' => $user->id,
            'created_by' => $user->id,
            'status' => 'completed',
            'completed_at' => now()->subHours(2),
            'is_urgent' => true,
        ]);
        $urgentCompleted->timestamps = false;
        $urgentCompleted->created_at = now()->subDays(3);
        $urgentCompleted->save();

        // File 4: Urgent unactioned file created 2 days ago
        $urgentUnactioned = FileRecord::create([
            'file_name' => 'Urgent Unactioned File',
            'file_number' => 'REC/004',
            'department_id' => $dept->id,
            'current_department_id' => $dept->id,
            'current_user_id' => $user->id,
            'created_by' => $user->id,
            'status' => 'active',
            'is_urgent' => true,
        ]);
        $urgentUnactioned->timestamps = false;
        $urgentUnactioned->created_at = now()->subDays(2);
        $urgentUnactioned->save();

        $orderedFiles = FileRecord::prioritizeUrgentAndRecent()->get();

        // 1st: Urgent unactioned file
        $this->assertEquals($urgentUnactioned->id, $orderedFiles->get(0)->id);
        // 2nd: Most recently added file (New Normal)
        $this->assertEquals($newNormal->id, $orderedFiles->get(1)->id);
        // 3rd: Urgent completed file (placed by created_at since it has been actioned)
        $this->assertEquals($urgentCompleted->id, $orderedFiles->get(2)->id);
        // 4th: Oldest normal file
        $this->assertEquals($oldNormal->id, $orderedFiles->get(3)->id);
    }

    public function test_files_index_page_displays_urgent_unactioned_files_at_top()
    {
        $dept = Department::create(['name' => 'Records', 'code' => 'REC', 'is_active' => true]);
        $user = User::factory()->create([
            'department_id' => $dept->id,
            'role' => 'admin',
            'is_active' => true,
        ]);

        $newNormal = FileRecord::create([
            'file_name' => 'New Normal File',
            'file_number' => 'REC/101',
            'department_id' => $dept->id,
            'current_department_id' => $dept->id,
            'current_user_id' => $user->id,
            'created_by' => $user->id,
            'status' => 'active',
            'is_urgent' => false,
            'created_at' => now()->subDay(),
        ]);

        $urgentUnactioned = FileRecord::create([
            'file_name' => 'Urgent Unactioned File',
            'file_number' => 'REC/102',
            'department_id' => $dept->id,
            'current_department_id' => $dept->id,
            'current_user_id' => $user->id,
            'created_by' => $user->id,
            'status' => 'active',
            'is_urgent' => true,
            'created_at' => now()->subDays(4),
        ]);

        $response = $this->actingAs($user)->get(route('files.index'));

        $response->assertStatus(200);
        $files = $response->viewData('files');
        $this->assertEquals($urgentUnactioned->id, $files->first()->id);
    }
}
