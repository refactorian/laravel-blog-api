<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\V1\UploadMediaRequest;
use App\Http\Resources\MediaResource;
use App\Models\Media;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaController extends ApiController
{
    public function store(UploadMediaRequest $request): JsonResponse
    {
        $file = $request->file('file');
        $filename = Str::uuid().'.'.$file->getClientOriginalExtension();
        $path = $file->storeAs('media', $filename, 'public');

        $media = $request->user()->media()->create([
            'disk' => 'public',
            'path' => $path,
            'filename' => $filename,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'metadata' => [
                'original_name' => $file->getClientOriginalName(),
            ],
        ]);

        return $this->created(new MediaResource($media), 'Media uploaded successfully.');
    }

    public function destroy(Media $media, Request $request): JsonResponse
    {
        $this->authorize('delete', $media);

        Storage::disk($media->disk)->delete($media->path);
        $media->delete();

        return $this->noContent();
    }
}
