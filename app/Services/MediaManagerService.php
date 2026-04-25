<?php

namespace App\Services;

use App\Models\Cover;
use App\Models\Gallery;
use App\Models\Journey;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class MediaManagerService
{
    public function createJourney(array $data, UploadedFile $image): void
    {
        $path = $image->store('journeys', 's3');

        Journey::create([
            'title' => $data['title'],
            'story' => $data['story'],
            'image_url' => $path,
            'journey_date' => $data['journey_date'],
        ]);
    }

    public function updateJourney(array $data, ?UploadedFile $image): void
    {
        $journey = Journey::query()->findOrFail($data['id']);
        $newImagePath = $journey->getRawOriginal('image_url');

        if ($image) {
            $oldPath = $journey->getRawOriginal('image_url');
            if ($oldPath) {
                Storage::disk('s3')->delete($oldPath);
            }

            $newImagePath = $image->store('journeys', 's3');
        }

        $journey->update([
            'title' => $data['title'],
            'story' => $data['story'],
            'image_url' => $newImagePath,
            'journey_date' => $data['journey_date'],
        ]);
    }

    public function createGallery(UploadedFile $image): void
    {
        $path = $image->store('galleries', 's3');
        Gallery::create([
            'image_url' => $path,
        ]);
    }

    public function updateGallery(array $data, ?UploadedFile $image): void
    {
        if (! $image) {
            return;
        }

        $gallery = Gallery::query()->findOrFail($data['id']);
        $oldPath = $gallery->getRawOriginal('image_url');
        if ($oldPath) {
            Storage::disk('s3')->delete($oldPath);
        }

        $newImagePath = $image->store('galleries', 's3');
        $gallery->update([
            'image_url' => $newImagePath,
        ]);
    }

    public function upsertCover(UploadedFile $image): void
    {
        $existingCover = Cover::query()->latest()->first();
        $existingPath = $existingCover?->getRawOriginal('image_url');
        if ($existingPath) {
            Storage::disk('s3')->delete($existingPath);
        }

        $path = $image->store('covers', 's3');
        Cover::updateOrCreate(
            ['singleton' => true],
            ['image_url' => $path],
        );
    }
}
