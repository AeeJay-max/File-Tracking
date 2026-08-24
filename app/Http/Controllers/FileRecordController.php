<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\FileMovement;
use App\Models\FileRecord;
use App\Models\FileTransfer;
use App\Models\Folder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class FileRecordController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = FileRecord::with(['department', 'currentDepartment', 'creator', 'currentHolder', 'folder']);

        // Check if user belongs to Records department
        $isRecordsDept = false;
        if ($user->department) {
            $code = strtoupper((string) $user->department->code);
            $name = Str::lower((string) $user->department->name);
            if ($code === 'REC' || $name === 'records' || Str::contains($name, 'record')) {
                $isRecordsDept = true;
            }
        }

        // Folder filtering
        $selectedFolder = null;
        if ($request->filled('folder_id')) {
            $selectedFolder = Folder::find($request->folder_id);
            if ($selectedFolder) {
                $query->where('folder_id', $selectedFolder->id);
            }
        }

        if ($user->role === 'super_admin') {
            // SuperAdmin is strictly for user account management — no file access
            $query->whereRaw('1 = 0');
        } elseif (! $isRecordsDept) {
            // Non-Records department users can ONLY see files created in, currently in, or transferred to/from their department
            if ($user->department_id) {
                $deptFileIds = FileMovement::where('from_department', $user->department_id)
                    ->orWhere('to_department', $user->department_id)
                    ->pluck('file_id')->unique()->values();

                $involvedFileIds = FileTransfer::where(fn ($q) => $q
                    ->where('sender_id', $user->id)
                    ->orWhere('receiver_id', $user->id))
                    ->pluck('file_id')->unique()->values();

                $query->where(fn ($q) => $q
                    ->where('current_department_id', $user->department_id)
                    ->orWhere('department_id', $user->department_id)
                    ->orWhere('created_by', $user->id)
                    ->orWhere('current_user_id', $user->id)
                    ->orWhereIn('id', $deptFileIds)
                    ->orWhereIn('id', $involvedFileIds));
            } else {
                $query->where(fn ($q) => $q
                    ->where('created_by', $user->id)
                    ->orWhere('current_user_id', $user->id));
            }
        }

        if ($request->filled('search')) {
            $s = $request->string('search')->trim()->value();
            $query->where(fn ($q) => $q
                ->where('file_name', 'like', "%{$s}%")
                ->orWhere('file_number', 'like', "%{$s}%"));
        }

        if ($request->filled('status')) {
            $allowed = ['active', 'archived', 'draft', 'pending_assignment'];
            if (in_array($request->status, $allowed, true)) {
                $query->where('status', $request->status);
            }
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->date('from_date'));
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->date('to_date'));
        }

        // For Records department: if no specific folder or search query is requested, show Folders overview
        $showFoldersView = $isRecordsDept && !$request->filled('folder_id') && !$request->filled('search') && !$request->filled('status');

        $folders = collect();
        if ($showFoldersView) {
            $folders = Folder::withCount('files')->latest()->paginate(20)->withQueryString();
        }

        // Sort urgent files first, then files with upcoming return_deadline, then latest created
        $files = $query->orderBy('is_urgent', 'desc')
                       ->orderByRaw('CASE WHEN return_deadline IS NULL THEN 1 ELSE 0 END, return_deadline ASC')
                       ->latest()
                       ->paginate(20)
                       ->withQueryString();

        return view('files.index', compact('files', 'folders', 'isRecordsDept', 'showFoldersView', 'selectedFolder'));
    }

    public function create()
    {
        // Any authenticated user with can_create_file permission may create
        $this->authorize('create', FileRecord::class);
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        $folders = Folder::orderBy('folder_number')->get();

        return view('files.create', compact('departments', 'folders'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', FileRecord::class);

        $normalizedFileNumber = strtoupper(trim((string) $request->input('file_number', '')));
        $normalizedFolderNumber = strtoupper(trim((string) $request->input('folder_number', '')));
        $folderName = trim((string) $request->input('folder_name', ''));

        $request->merge([
            'file_number' => $normalizedFileNumber,
            'folder_number' => $normalizedFolderNumber,
        ]);

        $request->validate([
            'file_number' => [
                'required',
                'string',
                'max:100',
                'regex:/^[A-Za-z0-9\-\/\._ ]+$/',
                Rule::unique('file_records', 'file_number')
                    ->where(fn ($query) => $query->where('department_id', (int) $request->input('department_id'))),
            ],
            'folder_number' => 'required|string|max:100',
            'folder_name' => 'required|string|max:255',
            'file_name' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'remarks' => 'nullable|string|max:1000',
            'attachment' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png|max:10240',
        ], [
            'file_number.unique' => 'This File Number already exists in this department. Use a different file number or select a different department.',
            'file_number.regex' => 'File number may only contain letters, numbers, hyphens, slashes, dots and spaces.',
            'folder_number.required' => 'Please select or enter a Folder Number for this file.',
            'folder_name.required' => 'Please enter or select a Folder Name.',
        ]);

        $deptId = (int) $request->department_id;

        // Find or create folder
        $folder = Folder::firstOrCreate(
            ['folder_number' => $normalizedFolderNumber],
            [
                'folder_name' => $folderName,
                'department_id' => $deptId,
                'created_by' => Auth::id(),
            ]
        );

        $file = FileRecord::create([
            'created_by' => Auth::id(),
            'current_user_id' => Auth::id(),
            'department_id' => $deptId,
            'current_department_id' => $deptId,
            'folder_id' => $folder->id,
            'file_name' => $request->string('file_name')->trim()->value(),
            'file_number' => $normalizedFileNumber,
            'remarks' => $request->string('remarks')->trim()->value() ?: null,
            'status' => 'active',
        ]);

        if ($request->hasFile('attachment')) {
            $uploaded = $request->file('attachment');
            $storedName = Str::uuid()->toString().'.'.$uploaded->extension();
            $path = $uploaded->storeAs('files/'.$file->uuid, $storedName, 'private');

            $file->update([
                'attachment_path' => $path,
                'attachment_name' => $uploaded->getClientOriginalName(),
                'attachment_mime' => $uploaded->getClientMimeType(),
            ]);
        }

        FileMovement::create([
            'file_id' => $file->id,
            'from_user' => Auth::id(),
            'to_user' => Auth::id(),
            'from_department' => $deptId,
            'to_department' => $deptId,
            'action' => 'created',
            'remarks' => 'File created by '.Auth::user()->name.' in folder '.$folder->folder_number,
        ]);

        return redirect()->route('files.index')->with('success', 'File "'.$file->file_number.'" created successfully in folder "'.$folder->folder_number.'".');
    }

    public function edit(FileRecord $file)
    {
        $this->authorize('update', $file);

        return view('files.edit', compact('file'));
    }

    public function update(Request $request, FileRecord $file)
    {
        $this->authorize('update', $file);

        $request->validate([
            'file_name' => 'required|string|max:255',
            'remarks' => 'nullable|string|max:1000',
            'attachment' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png|max:10240',
        ]);

        $file->update([
            'file_name' => $request->string('file_name')->trim()->value(),
            'remarks' => $request->string('remarks')->trim()->value() ?: null,
        ]);

        FileMovement::create([
            'file_id' => $file->id,
            'from_user' => Auth::id(),
            'to_user' => Auth::id(),
            'from_department' => Auth::user()->department_id,
            'to_department' => Auth::user()->department_id,
            'action' => 'updated',
            'remarks' => 'Document contents updated by '.Auth::user()->name,
        ]);

        if ($request->hasFile('attachment')) {
            if ($file->attachment_path && Storage::disk('private')->exists($file->attachment_path)) {
                Storage::disk('private')->delete($file->attachment_path);
            }

            $uploaded = $request->file('attachment');
            $storedName = Str::uuid()->toString().'.'.$uploaded->extension();
            $path = $uploaded->storeAs('files/'.$file->uuid, $storedName, 'private');

            $file->update([
                'attachment_path' => $path,
                'attachment_name' => $uploaded->getClientOriginalName(),
                'attachment_mime' => $uploaded->getClientMimeType(),
            ]);
        }

        return redirect()->route('files.show', $file->uuid)->with('success', 'Document contents updated successfully.');
    }

    public function show(FileRecord $file)
    {
        $this->authorize('view', $file);

        $file->load([
            'department',
            'currentDepartment',
            'creator',
            'currentHolder',
            'movements.fromUser',
            'movements.toUser',
            'movements.fromDept',
            'movements.toDept',
        ]);

        return view('files.show', compact('file'));
    }

    public function download(FileRecord $file)
    {
        $this->authorize('download', $file);

        if (! $file->attachment_path || ! Storage::disk('private')->exists($file->attachment_path)) {
            return redirect()->route('files.show', $file->uuid)
                ->with('error', 'Attachment not found.');
        }

        return Storage::disk('private')->download(
            $file->attachment_path,
            $file->attachment_name ?: $file->file_name
        );
    }
}
