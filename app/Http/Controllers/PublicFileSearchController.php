<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\FileRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

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
     * Only publicly viewable files (is_public = true) are returned.
     * Returns strictly safe, minimal public fields — zero internal staff data, holder info, or remarks.
     */
    public function search(Request $request)
    {
        $request->validate([
            'file_number' => 'required|string|max:100',
            'department_uuid' => 'nullable|string|exists:departments,uuid',
        ]);

        $fileNumber = strtoupper(trim($request->string('file_number')->value()));
        $departmentUuid = $request->string('department_uuid')->trim()->value();

        // Check cache or retrieve public file record
        $cacheKey = 'public_file_'.$fileNumber.($departmentUuid ? '_'.$departmentUuid : '');

        $data = Cache::remember($cacheKey, 3600, function () use ($fileNumber, $departmentUuid) {
            $query = FileRecord::where('file_number', $fileNumber)
                ->where('is_public', true);

            if ($departmentUuid !== '') {
                $query->whereHas('department', fn ($q) => $q->where('uuid', $departmentUuid));
            }

            $matches = $query
                ->with([
                    'department',
                    'currentDepartment',
                    'movements' => fn ($q) => $q->with(['fromDept', 'toDept'])->orderBy('created_at'),
                ])
                ->orderBy('id')
                ->get();

            if ($matches->isEmpty()) {
                return null;
            }

            if ($matches->count() > 1 && $departmentUuid === '') {
                return [
                    'ambiguous' => true,
                    'choices' => $matches->map(fn (FileRecord $record) => [
                        'uuid' => $record->department?->uuid,
                        'name' => $record->department?->name ?? 'Unknown Department',
                    ])->unique('uuid')->values(),
                ];
            }

            $file = $matches->first();

            // Format human-readable status
            $statusText = match ($file->status) {
                'completed' => 'Completed / Closed',
                'pending_assignment' => 'In Transit / Pending Department Handling',
                'active' => 'In Progress / Active Handling',
                'archived' => 'Archived',
                default => ucwords(str_replace('_', ' ', (string) $file->status)),
            };

            // Public safe response array (DTO)
            $result = [
                'file_number' => $file->file_number,
                'file_name' => $file->file_name,
                'title' => $file->file_name,
                'origin_department' => $file->department->name ?? 'N/A',
                'current_department' => $file->currentDepartment->name ?? ($file->department->name ?? 'N/A'),
                'status' => $statusText,
                'registered_at' => $file->created_at->format('d M Y'),
                'created_date' => $file->created_at->format('d M Y'),
            ];

            $journey = $this->buildPublicJourney($file->movements);

            return [
                'ambiguous' => false,
                'result' => $result,
                'journey' => $journey,
            ];
        });

        if (! $data) {
            return back()
                ->withInput()
                ->with('search_error', 'No file found with this File Number for the selected department.');
        }

        if (isset($data['ambiguous']) && $data['ambiguous'] === true) {
            return back()
                ->withInput()
                ->with('search_error', 'Multiple public files were found with this File Number. Select a department to view the correct file.')
                ->with('department_choices', $data['choices']);
        }

        $result = $data['result'];
        $journey = $data['journey'];

        $departments = Department::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'uuid', 'name']);

        return view('public.file-search', compact('result', 'journey', 'departments'))->with('searched', true);
    }

    /**
     * Collapse raw FileMovement records into safe department-level milestones.
     * Absolutely NO staff names, user IDs, employee numbers, emails, or internal remarks are exposed.
     */
    private function buildPublicJourney($movements): array
    {
        $journey = [];
        $currentDept = null;
        $currentDate = null;
        $currentTime = null;

        foreach ($movements as $move) {
            if ($move->action === 'created') {
                $deptName = $move->fromDept?->name ?? 'Origin Department';
                $date = $move->created_at->format('d M Y');
                $time = $move->created_at->format('h:i A');
            } else {
                $deptName = $move->toDept?->name ?? 'Handling Department';
                $date = $move->created_at->format('d M Y');
                $time = $move->created_at->format('h:i A');
            }

            if ($deptName !== $currentDept) {
                if ($currentDept !== null) {
                    $journey[] = [
                        'dept_name' => $currentDept,
                        'date' => $currentDate,
                        'time' => $currentTime,
                        'action' => count($journey) === 0 ? 'Registered' : 'Transferred',
                        'is_current' => false,
                    ];
                }

                $currentDept = $deptName;
                $currentDate = $date;
                $currentTime = $time;
            }
        }

        if ($currentDept !== null) {
            $journey[] = [
                'dept_name' => $currentDept,
                'date' => $currentDate,
                'time' => $currentTime,
                'action' => count($journey) === 0 ? 'Registered' : 'Current Handling Location',
                'is_current' => true,
            ];
        }

        return $journey;
    }
}
