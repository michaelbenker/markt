<?php

namespace App\Http\Controllers;

use App\Models\Medien;
use App\Models\Aussteller;
use App\Models\Anfrage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class MedienController extends Controller
{
    public function upload(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|max:10240', // 10MB
                'category' => 'required|in:angebot,stand,werkstatt,vita',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'aussteller_id' => 'nullable|exists:aussteller,id',
                'anfrage_id' => 'nullable|exists:anfrage,id',
            ]);

            $file = $request->file('file');
            $category = $request->input('category');
            
            // Mediable Model bestimmen
            $mediableType = null;
            $mediableId = null;
            
            if ($request->filled('aussteller_id')) {
                $mediableType = Aussteller::class;
                $mediableId = $request->input('aussteller_id');
            } elseif ($request->filled('anfrage_id')) {
                $mediableType = Anfrage::class;
                $mediableId = $request->input('anfrage_id');
            } else {
                return response()->json(['error' => 'Aussteller oder Anfrage ID erforderlich'], 400);
            }

            // Datei speichern
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $directory = $mediableType === Aussteller::class ? 'aussteller' : 'anfragen';
            $path = $file->storeAs($directory . '/' . $category, $filename, 'public');

            // Höchste sort_order ermitteln
            $maxSortOrder = Medien::where('mediable_type', $mediableType)
                ->where('mediable_id', $mediableId)
                ->where('category', $category)
                ->max('sort_order') ?? 0;

            // Medien-Eintrag erstellen
            $medium = Medien::create([
                'mediable_type' => $mediableType,
                'mediable_id' => $mediableId,
                'category' => $category,
                'title' => $request->input('title'),
                'description' => $request->input('description'),
                'mime_type' => $file->getMimeType(),
                'file_extension' => $file->getClientOriginalExtension(),
                'path' => $path,
                'size' => $file->getSize(),
                'sort_order' => $maxSortOrder + 1,
            ]);

            return response()->json([
                'success' => true,
                'medium' => $medium
            ]);

        } catch (\Exception $e) {
            Log::error('Medien Upload Fehler: ' . $e->getMessage(), [
                'request' => $request->all(),
                'exception' => $e
            ]);

            return response()->json([
                'error' => 'Upload fehlgeschlagen: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $medium = Medien::findOrFail($id);
            
            // Datei vom Storage löschen
            if (Storage::disk('public')->exists($medium->path)) {
                Storage::disk('public')->delete($medium->path);
            }
            
            // Datenbank-Eintrag löschen
            $medium->delete();
            
            return response()->json(['success' => true]);
            
        } catch (\Exception $e) {
            Log::error('Medien Lösch-Fehler: ' . $e->getMessage());
            
            return response()->json([
                'error' => 'Löschen fehlgeschlagen: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateOrder(Request $request)
    {
        try {
            $request->validate([
                'medien' => 'required|array',
                'medien.*.id' => 'required|exists:medien,id',
                'medien.*.sort_order' => 'required|integer|min:0',
            ]);

            foreach ($request->input('medien') as $mediumData) {
                Medien::where('id', $mediumData['id'])
                    ->update(['sort_order' => $mediumData['sort_order']]);
            }

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('Medien Sortierung Fehler: ' . $e->getMessage());
            
            return response()->json([
                'error' => 'Sortierung fehlgeschlagen: ' . $e->getMessage()
            ], 500);
        }
    }
}