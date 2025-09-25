<?php

namespace App\Http\Controllers;

use App\Http\Resources\MediaResource;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    public function index(Request $request)
    {
        $media = Media::latest()->paginate(20);
        return MediaResource::collection($media);
    }

    // 🔹 POST /api/media/upload
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:20480', // max 20MB
        ]);

        $file = $request->file('file');
        $path = $file->store('uploads', 'public');

        $media = Media::create([
            'file_name'     => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type'     => $file->getClientMimeType(),
            'size'          => $file->getSize(),
            'url'           => str_replace('public/', '', $path),
            'disk'          => 'public',
        ]);

        return new MediaResource($media);
    }

    // 🔹 DELETE /api/media/{id}
    public function destroy(Media $media)
    {
        Storage::disk($media->disk)->delete($media->file_name);
        $media->delete();

        return response()->json(['message' => 'Media deleted successfully']);
    }
}
