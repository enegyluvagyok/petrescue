<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AppBuild;
use Illuminate\Support\Facades\Storage;

class AppBuildController extends Controller
{
    public function index()
    {
        $builds = AppBuild::orderByDesc('created_at')->get();
        return view('app_builds.index', compact('builds'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'apk' => 'required|file|mimes:apk,zip|max:512000', // 500 MB
            'version' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:500',
            'build_type' => 'nullable|string|max:20',
        ]);

        // Feltöltés storage-ba
        $file = $request->file('apk');
        $path = $file->store('public/apks');
        $fileName = basename($path);

        // Mentés DB-be
        AppBuild::create([
            'file_name' => $fileName,
            'original_name' => $file->getClientOriginalName(),
            'version' => $request->input('version'),
            'notes' => $request->input('notes'),
            'build_type' => $request->input('build_type', 'release'),
        ]);

        return redirect()->route('builds.index')->with('success', '✅ APK sikeresen feltöltve!');
    }

    public function download(AppBuild $build)
    {
        return Storage::download("public/apks/{$build->file_name}", $build->original_name);
    }

    public function destroy(AppBuild $build)
    {
        Storage::delete("public/apks/{$build->file_name}");
        $build->delete();

        return back()->with('success', '🗑️ Build törölve!');
    }
}
