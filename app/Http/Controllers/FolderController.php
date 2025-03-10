<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\Folder;
use Illuminate\Support\Facades\Cookie;

class FolderController extends Controller
{
    protected $baseDirectory = '';

    public function createFolder(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'folder_name' => 'required|string|max:255',
            'password' => 'nullable|string|', // Optional password
        ]);

        if ($validation->fails()) {
            return response()->json([
                'status' => 422,
                'message' => 'Nav norādīti nepieciešamie dati!',
                'errors' => $validation->errors()
            ], 422);
        }

        $folderName = $request->input('folder_name');
        $password = $request->input('password');

        if (Storage::disk('public')->exists($folderName)) {
            return response()->json(['error' => 'Mape jau pastāv'], 400);
        }

        Storage::disk('public')->makeDirectory($folderName, 0755, true);

        Folder::create([
            'name' => $folderName,
            'password' => $password
        ]);

        return response()->json([
            'message' => 'Mape izveidota veiksmīgi',
            'folder' => $folderName
        ], 201);
    }
    public function uploadFiles(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'files.*' => 'required|image|max:51200',
            'folder_name' => 'required|string|max:255'
        ]);

        if ($validation->fails()) {
            return response()->json([
                'status' => 422,
                'message' => 'Validation failed!',
                'errors' => $validation->errors()
            ], 422);
        }

        $folderName = $request->input('folder_name');
        $uploadedFileUrls = [];

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $fileName = $file->getClientOriginalName();
                $finalName = date("His") . '_' . $fileName;
                Storage::disk('public')->putFileAs($folderName, $file, $finalName);

                $imgUrl = Storage::url($folderName . '/' . $finalName);
                $uploadedFileUrls[] = $imgUrl;
            }
            return response()->json($uploadedFileUrls, 201);
        }

        return response()->json(['error' => 'No files uploaded'], 400);
    }

public function listFolders(Request $request)
{
    $folders = Storage::disk('public')->directories();

    $folderData = array_map(function ($folder) {
        $folderName = basename($folder);
        $folderRecord = Folder::where('name', $folderName)->first();

        return [
            'name' => $folderName,
            'has_password' => $folderRecord && $folderRecord->password ? true : false
        ];
    }, $folders);

    return response()->json(['folders' => $folderData]);
}

public function unlockFolder(Request $request)
{
    $validation = Validator::make($request->all(), [
        'folder_name' => 'required|string|max:255',
        'password' => 'required|string'
    ]);

    if ($validation->fails()) {
        return response()->json([
            'status' => 422,
            'message' => 'Validation failed!',
            'errors' => $validation->errors()
        ], 422);
    }

    $folderName = $request->input('folder_name');
    $password = $request->input('password');

    $folder = Folder::where('name', $folderName)->first();

    if (!$folder) {
        return response()->json(['error' => 'Folder not found'], 404);
    }

    \Log::info('Unlock attempt', [
        'folder_name' => $folderName,
        'input_password' => $password,
        'stored_password_hash' => $folder->password,
    ]);

    if (!$folder->password) {
        return response()->json(['message' => 'This folder is not password protected'], 200);
    }

    if (Hash::check($password, $folder->password)) {
        // Encrypt the cookie value before setting it
        $cookie = cookie()->forever('folder_' . $folderName . '_unlocked', encrypt('true'), 60, null, null, false, true); // Last 'true' is HttpOnly

        return response()->json(['message' => 'Folder unlocked successfully'], 200)
                         ->withCookie($cookie); // Attach the encrypted, HttpOnly cookie
    }
    return response()->json(['error' => 'Invalid password'], 401);
}
public function checkFolderUnlocked(Request $request)
{
    // Validate the folder_name parameter
    $validation = Validator::make($request->all(), [
        'folder_name' => 'required|string|max:255'
    ]);

    if ($validation->fails()) {
        return response()->json([
            'status' => 422,
            'message' => 'Validation failed!',
            'errors' => $validation->errors()
        ], 422);
    }

    $folderName = $request->input('folder_name');

    // Check if the folder exists in storage
    if (!Storage::disk('public')->exists($folderName)) {
        return response()->json(['error' => 'Folder not found in storage'], 404);
    }

    // Fetch the folder record from the database
    $folder = Folder::where('name', $folderName)->first();

    // If the folder doesn't have a password, skip cookie check
    if (!$folder || !$folder->password) {
        // No password, no need to check cookie, just proceed with fetching files
        $files = Storage::disk('public')->files($folderName);

        if (empty($files)) {
            return response()->json(['message' => 'No files found in this folder'], 404);
        }

        $fileUrls = array_map(function ($file) {
            return Storage::url($file);
        }, $files);

        return response()->json(['files' => $fileUrls]);
    }

    // If the folder has a password, check the cookie for unlocked status
    $cookieValue = $request->cookie('folder_' . $folderName . '_unlocked');

    if (!$cookieValue) {
        return response()->json(['error' => 'Folder is locked. Please unlock the folder first.'], 403);
    }

    try {
        $decryptedValue = decrypt($cookieValue);

        // Check if the folder is unlocked based on the cookie value
        if ($decryptedValue === 'true') {
            // Folder is unlocked, proceed with fetching the files
            $files = Storage::disk('public')->files($folderName);

            if (empty($files)) {
                return response()->json(['message' => 'No files found in this folder'], 404);
            }

            $fileUrls = array_map(function ($file) {
                return Storage::url($file);
            }, $files);

            return response()->json(['files' => $fileUrls]);

        } else {
            return response()->json(['error' => 'Folder is locked. Please unlock it to access the content.'], 403);
        }
    } catch (\Exception $e) {
        return response()->json(['error' => 'Invalid or tampered cookie'], 401);
    }
}




    public function retrieveFiles(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'folder_name' => 'required|string|max:255'
        ]);

        if ($validation->fails()) {
            return response()->json([
                'status' => 422,
                'message' => 'Validation failed!',
                'errors' => $validation->errors()
            ], 422);
        }

        $folderName = $request->input('folder_name');
        $files = Storage::disk('public')->files($folderName);

        if (empty($files)) {
            return response()->json(['message' => 'No files found in this folder'], 404);
        }

        $fileUrls = array_map(function ($file) {
            return Storage::url($file);
        }, $files);

        return response()->json(['files' => $fileUrls]);
    }
public function deleteFolder(Request $request)
{
    $validation = Validator::make($request->all(), [
        'folder_name' => 'required|string|max:255'
    ]);

    if ($validation->fails()) {
        return response()->json([
            'status' => 422,
            'message' => 'Validation failed!',
            'errors' => $validation->errors()
        ], 422);
    }

    $folderName = $request->input('folder_name');

    // Check if the folder exists in storage
    if (!Storage::disk('public')->exists($folderName)) {
        return response()->json(['error' => 'Folder not found in storage'], 404);
    }

    // Delete the folder from storage
    Storage::disk('public')->deleteDirectory($folderName);

    // Check if the folder exists in the database
    $folder = Folder::where('name', $folderName)->first();

    if ($folder) {
        // Delete the folder record from the database
        $folder->delete();
    } else {
        return response()->json(['error' => 'Folder not found in the database'], 404);
    }

    return response()->json(['message' => 'Folder deleted successfully'], 200);
}


    public function deleteFiles(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'folder_name' => 'required|string|max:255',
            'files' => 'required|array',
            'files.*' => 'string'
        ]);

        if ($validation->fails()) {
            return response()->json([
                'status' => 422,
                'message' => 'Validation failed!',
                'errors' => $validation->errors()
            ], 422);
        }

        $folderName = $request->input('folder_name');
        $files = $request->input('files');
        $missingFiles = [];
        $currentFiles = Storage::disk('public')->files($folderName);

        foreach ($files as $file) {
            $filePath = $folderName . '/' . $file;

            if (Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            } else {
                $missingFiles[] = $file;
            }
        }

        if (count($missingFiles) > 0) {
            return response()->json([
                'status' => 200,
                'message' => 'Some files were not found, but the rest were deleted.',
                'missing_files' => $missingFiles,
                'current_files' => array_map('basename', $currentFiles) // Only return file names
            ], 200);
        }

        return response()->json(['message' => 'All files deleted successfully'], 200);
    }
}
