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

        // 📦 Fájl beolvasása
        $file = $request->file('apk');

        // 🧩 Eredeti név és kiterjesztés megőrzése
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = strtolower($file->getClientOriginalExtension());

        // Biztonsági okból csak .apk-t engedünk ténylegesen
        if (!in_array($extension, ['apk'])) {
            return back()->with('error', '❌ Csak .apk fájl tölthető fel!');
        }

        // 🕒 Verziózott fájlnév generálása
        $timestamp = now()->format('Y-m-d_His');
        $fileName = "{$originalName}_{$timestamp}.{$extension}";

        // 🗂️ Mentés storage-ba
        $path = $file->storeAs('public/apks', $fileName);

        // 🧾 Mentés adatbázisba
        AppBuild::create([
            'file_name' => $fileName,
            'original_name' => $file->getClientOriginalName(),
            'version' => $request->input('version'),
            'notes' => $request->input('notes'),
            'build_type' => $request->input('build_type', 'release'),
        ]);

        return redirect()
            ->route('builds.index')
            ->with('success', "✅ APK sikeresen feltöltve: {$fileName}");
    }

    public function download(AppBuild $build)
    {
        $filePath = "public/apks/{$build->file_name}";

        if (!Storage::exists($filePath)) {
            return back()->with('error', '❌ A fájl nem található a szerveren.');
        }

        return Storage::download(
            $filePath,
            $build->original_name,
            ['Content-Type' => 'application/vnd.android.package-archive']
        );
    }

    public function destroy(AppBuild $build)
    {
        $filePath = "public/apks/{$build->file_name}";
        if (Storage::exists($filePath)) {
            Storage::delete($filePath);
        }

        $build->delete();

        return back()->with('success', "🗑️ {$build->original_name} törölve!");
    }
}
