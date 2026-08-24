<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class FolderController extends Controller
{
    private function isRecordsAdmin(): bool
    {
        $user = Auth::user();
        if (! $user || $user->role !== 'admin' || ! $user->department) {
            return false;
        }

        $code = strtoupper((string) $user->department->code);
        $name = Str::lower((string) $user->department->name);

        return $code === 'REC' || $name === 'records' || Str::contains($name, 'record');
    }

    public function index(Request $request)
    {
        $query = Folder::with(['department', 'creator'])->withCount('files');

        if ($request->filled('search')) {
            $s = $request->string('search')->trim()->value();
            $query->where(function ($q) use ($s) {
                $q->where('folder_number', 'like', "%{$s}%")
                  ->orWhere('folder_name', 'like', "%{$s}%");
            });
        }

        $folders = $query->latest()->paginate(20)->withQueryString();

        return view('folders.index', compact('folders'));
    }

    public function store(Request $request)
    {
        if (! $this->isRecordsAdmin()) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Only the Records Department Admin is permitted to create folders.'], 403);
            }
            return redirect()->back()->with('error', 'Only the Records Department Admin is permitted to create folders.');
        }

        $normalizedNumber = strtoupper(trim((string) $request->input('folder_number', '')));
        $request->merge(['folder_number' => $normalizedNumber]);

        $request->validate([
            'folder_number' => 'required|string|max:100|unique:folders,folder_number',
            'folder_name' => 'required|string|max:255',
        ]);

        $folder = Folder::create([
            'folder_number' => $normalizedNumber,
            'folder_name' => $request->string('folder_name')->trim()->value(),
            'department_id' => Auth::user()->department_id,
            'created_by' => Auth::id(),
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'folder' => [
                    'id' => $folder->id,
                    'folder_number' => $folder->folder_number,
                    'folder_name' => $folder->folder_name,
                ],
            ]);
        }

        return redirect()->back()->with('success', 'Folder "'.$folder->folder_number.'" created successfully.');
    }

    public function storeAjax(Request $request)
    {
        if (! $this->isRecordsAdmin()) {
            return response()->json(['success' => false, 'message' => 'Only the Records Department Admin is permitted to create folders.'], 403);
        }

        $normalizedNumber = strtoupper(trim((string) $request->input('folder_number', '')));
        $request->merge(['folder_number' => $normalizedNumber]);

        $request->validate([
            'folder_number' => 'required|string|max:100|unique:folders,folder_number',
            'folder_name' => 'required|string|max:255',
        ]);

        $folder = Folder::create([
            'folder_number' => $normalizedNumber,
            'folder_name' => $request->string('folder_name')->trim()->value(),
            'department_id' => Auth::user()->department_id,
            'created_by' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'folder' => [
                'id' => $folder->id,
                'folder_number' => $folder->folder_number,
                'folder_name' => $folder->folder_name,
            ],
        ]);
    }

    public function getDetails(Request $request)
    {
        $number = strtoupper(trim((string) $request->input('folder_number', '')));
        $id = $request->input('folder_id');

        $folder = null;
        if ($id) {
            $folder = Folder::find($id);
        } elseif ($number) {
            $folder = Folder::where('folder_number', $number)->first();
        }

        if (! $folder) {
            return response()->json(['success' => false, 'message' => 'Folder not found'], 404);
        }

        return response()->json([
            'success' => true,
            'folder' => [
                'id' => $folder->id,
                'folder_number' => $folder->folder_number,
                'folder_name' => $folder->folder_name,
            ],
        ]);
    }
}
