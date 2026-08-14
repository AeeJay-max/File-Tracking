<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\FileRecord;
use Illuminate\Http\Request;

class PublicFileSearchController extends Controller
{
    /** Show the public file search page. */
    public function index()
    {
        $departments = Department::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'uuid', 'name']);

        return view('public.file-search', compact('departments'));
    }

    /**
     * Search for a file by file number.
     * Returns only safe public fields — no internal user data.
     * The file journey is collapsed to department-level milestones.
     */
    public function search(Request $request)
    {
        $request->validate([
            'file_number' => 'required|string|max:100',
            'department_uuid' => 'nullable|string|exists:departments,uuid',
        ]);

        $fileNumber = strtoupper(trim($request->string('file_number')->value()));
        $departmentUuid = $request->string('department_uuid')->trim()->value();

        $query = FileRecord::where('file_number', $fileNumber);

        if ($departmentUuid !== '') {
            $query->whereHas('department', fn ($q) => $q->where('uuid', $departmentUuid));
        }

        $matches = $query
            ->with([
                'department',
                'currentDepartment',
                'currentHolder',
                'movements' => fn ($q) => $q->with(['fromDept', 'toDept'])->orderBy('created_at'),
            ])
            ->orderBy('id')
            ->get();

        if ($matches->isEmpty()) {
            return back()
                ->withInput()
                ->with('search_error', 'No file found with this File Number for the selected department.');
        }

        if ($matches->count() > 1) {
            $departmentChoices = $matches
                ->map(fn (FileRecord $record) => [
                    'uuid' => $record->department?->uuid,
                    'name' => $record->department?->name ?? 'Unknown Department',
                ])
                ->unique('uuid')
                ->values();

            return back()
                ->withInput()
                ->with('search_error', 'Multiple files were found with this File Number. Select a department to view the correct file.')
                ->with('department_choices', $departmentChoices);
        }

        $file = $matches->first();

        $holder = $file->currentHolder;
        $holderName = $holder ? $holder->name : 'N/A';

        // ── Safe public file summary ─────────────────────────────────────
        $result = [
            'file_number' => $file->file_number,
            'file_name' => $file->file_name,
            'origin_department' => $file->department->name ?? 'N/A',
            'current_department' => $file->currentDepartment->name ?? ($file->department->name ?? 'N/A'),
            'current_holder' => $holderName,
            'status' => ucwords(str_replace('_', ' ', $file->status)),
            'created_date' => $file->created_at->format('d M Y'),
        ];

        // ── Build public department-level journey ────────────────────────
        // Rules:
        //  1. Collapse consecutive movements in the same department into ONE node.
        //  2. A new node is created whenever the department changes.
        //  3. Never expose: user names, employee IDs, emails, internal usernames.
        //  4. Remarks are safe to show (they are file-level notes, not user-internal data).
        //     We use the last remark recorded while the file was in that department.
        $journey = $this->buildPublicJourney($file->movements);

        $departments = Department::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'uuid', 'name']);

        return view('public.file-search', compact('result', 'journey', 'departments'))->with('searched', true);
    }

    /**
     * Collapse raw FileMovement records into department-level milestones.
     *
     * Each milestone contains:
     *   dept_name   — department name
     *   date        — date the file arrived in this dept (first movement into it)
     *   time        — time it arrived
     *   action      — 'Created' | 'Received' | 'Current'
     *   remark      — last non-null remarks recorded while in this dept
     *   is_current  — true for the last milestone (current department)
     */
    private function buildPublicJourney($movements): array
    {
        $journey = [];
        $currentDept = null;
        $currentDate = null;
        $currentTime = null;
        $lastRemark = null;

        foreach ($movements as $move) {
            // Determine the relevant department for this movement
            if ($move->action === 'created') {
                $deptName = $move->fromDept?->name ?? 'Unknown Department';
                $date = $move->created_at->format('d M Y');
                $time = $move->created_at->format('h:i A');
                $action = 'Created';
                $remark = $move->remarks;
            } else {
                $deptName = $move->toDept?->name ?? 'Unknown Department';
                $date = $move->created_at->format('d M Y');
                $time = $move->created_at->format('h:i A');
                $action = 'Received';
                $remark = $move->remarks;
            }

            if ($deptName !== $currentDept) {
                // Department changed — push previous node if any
                if ($currentDept !== null) {
                    $journey[] = [
                        'dept_name' => $currentDept,
                        'date' => $currentDate,
                        'time' => $currentTime,
                        'action' => count($journey) === 0 ? 'Created' : 'Received',
                        'remark' => $lastRemark,
                        'is_current' => false,
                    ];
                }

                $currentDept = $deptName;
                $currentDate = $date;
                $currentTime = $time;
                $lastRemark = $remark ?: null;
            } else {
                // Still in same dept — update remark if this one is more informative
                if ($remark) {
                    $lastRemark = $remark;
                }
            }
        }

        // Push the final (current) dept node
        if ($currentDept !== null) {
            $journey[] = [
                'dept_name' => $currentDept,
                'date' => $currentDate,
                'time' => $currentTime,
                'action' => count($journey) === 0 ? 'Created' : 'Current',
                'remark' => $lastRemark,
                'is_current' => true,
            ];
        }

        return $journey;
    }
}
