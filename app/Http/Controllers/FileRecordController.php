<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\FileMovement;
use App\Models\FileRecord;
use App\Models\FileTransfer;
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
        $query = FileRecord::with(['department', 'creator', 'currentHolder']);

        if ($user->role === 'user') {
            $involvedFileIds = FileTransfer::where(fn ($q) => $q
                ->where('sender_id', $user->id)
                ->orWhere('receiver_id', $user->id))
                ->pluck('file_id')->unique()->values();

            $query->where(fn ($q) => $q
                ->where('created_by', $user->id)
                ->orWhere('current_user_id', $user->id)
                ->orWhereIn('id', $involvedFileIds));
        } elseif ($user->role === 'admin') {
            // Admin sees all files in their department (current_department_id),
            // including unassigned (pending_assignment) files awaiting their action.
            $query->where('current_department_id', $user->department_id);
        }
        // super_admin sees all

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

        $files = $query->latest()->paginate(20)->withQueryString();

        return view('files.index', compact('files'));
    }

    public function create()
    {
        // Any authenticated user with can_create_file permission may create
        $this->authorize('create', FileRecord::class);
        $departments = Department::where('is_active', true)->orderBy('name')->get();

        return view('files.create', compact('departments'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', FileRecord::class);

        $normalizedFileNumber = strtoupper(trim((string) $request->input('file_number', '')));
        $request->merge(['file_number' => $normalizedFileNumber]);

        $request->validate([
            'file_number' => [
                'required',
                'string',
                'max:100',
                'regex:/^[A-Za-z0-9\-\/\._ ]+$/',
                Rule::unique('file_records', 'file_number')
                    ->where(fn ($query) => $query->where('department_id', (int) $request->input('department_id'))),
            ],
            'file_name' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'remarks' => 'nullable|string|max:1000',
            'attachment' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png|max:10240',
        ], [
            'file_number.unique' => 'This File Number already exists in this department. Use a different file number or select a different department.',
            'file_number.regex' => 'File number may only contain letters, numbers, hyphens, slashes, dots and spaces.',
        ]);

        $deptId = (int) $request->department_id;

        $file = FileRecord::create([
            'created_by' => Auth::id(),
            'current_user_id' => Auth::id(),
            'department_id' => $deptId,
            'current_department_id' => $deptId,
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
            'remarks' => 'File created by '.Auth::user()->name,
        ]);

        return redirect()->route('files.index')->with('success', 'File "'.$file->file_number.'" created successfully.');
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

        return redirect()->route('files.show', $file->uuid)->with('success', 'File updated successfully.');
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
