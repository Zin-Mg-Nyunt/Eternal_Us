<?php

namespace App\Http\Controllers;

use App\Models\Cover;
use App\Models\Gallery;
use App\Models\Journey;
use App\Models\Wish;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ActionController extends Controller
{
    public function home(): Response
    {
        $journeys = Journey::query()
            ->get()
            ->map(fn (Journey $journey) => [
                'id' => $journey->id,
                'title' => $journey->title,
                'story' => $journey->story,
                'image' => $journey->image_url,
                'journeyDate' => optional($journey->journey_date)->format('Y-m-d'),
                'date' => optional($journey->journey_date)->format('M d, Y')
                    ?? optional($journey->created_at)->format('M d, Y')
                    ?? 'Today',
            ]);

        $galleries = Gallery::query()
            ->latest()
            ->take(12)
            ->get()
            ->map(fn (Gallery $gallery) => [
                'id' => $gallery->id,
                'image' => $gallery->image_url,
            ]);

        $cover = Cover::query()->latest()->first();
        $tones = ['bg-rose-100', 'bg-pink-100', 'bg-fuchsia-100', 'bg-rose-50'];
        $wishes = Wish::query()
            ->latest()
            ->with('user:id,name')
            ->get()
            ->values()
            ->map(fn (Wish $wish, int $index) => [
                'id' => $wish->id,
                'name' => $wish->user?->name ?? 'Unknown',
                'message' => $wish->message,
                'tone' => $tones[$index % count($tones)],
            ]);

        return Inertia::render('Welcome', [
            'journeyItems' => $journeys,
            'galleryItems' => $galleries,
            'galleryPages' => Inertia::scroll(function () {
                $paginator = Gallery::query()->latest()->paginate(20);
                $paginator->setCollection(
                    $paginator->getCollection()->map(fn (Gallery $gallery) => [
                        'id' => $gallery->id,
                        'image' => $gallery->image_url,
                    ]),
                );

                return $paginator;
            }),
            'coverImage' => $cover?->image_url,
            'wishes' => $wishes,
        ]);
    }

    public function addJourney(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'story' => 'required|string|max:2000',
            'journey_date' => 'required|date',
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $path = $request->file('image')->store('journeys', 's3');

        Journey::create([
            'title' => $validated['title'],
            'story' => $validated['story'],
            'image_url' => $path,
            'journey_date' => $validated['journey_date'],
        ]);

        return back();

    }

    public function updateJourney(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|integer|exists:journeys,id',
            'title' => 'required|string|max:255',
            'story' => 'required|string|max:2000',
            'journey_date' => 'required|date',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $journey = Journey::query()->findOrFail($validated['id']);
        $newImagePath = $journey->getRawOriginal('image_url');

        if ($request->hasFile('image')) {
            $oldPath = $journey->getRawOriginal('image_url');
            if ($oldPath) {
                Storage::disk('s3')->delete($oldPath);
            }

            $newImagePath = $request->file('image')->store('journeys', 's3');
        }

        $journey->update([
            'title' => $validated['title'],
            'story' => $validated['story'],
            'image_url' => $newImagePath,
            'journey_date' => $validated['journey_date'],
        ]);

        return back();
    }

    public function addGallery(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $path = $request->file('image')->store('galleries', 's3');

        Gallery::create([
            'image_url' => $path,
        ]);

        return back();
    }

    public function updateGallery(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|integer|exists:galleries,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $gallery = Gallery::query()->findOrFail($validated['id']);

        if ($request->hasFile('image')) {
            $oldPath = $gallery->getRawOriginal('image_url');
            if ($oldPath) {
                Storage::disk('s3')->delete($oldPath);
            }

            $newImagePath = $request->file('image')->store('galleries', 's3');
            $gallery->update([
                'image_url' => $newImagePath,
            ]);
        }

        return back();
    }

    public function addCover(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $existingCover = Cover::query()->latest()->first();
        $existingPath = $existingCover?->getRawOriginal('image_url');
        if ($existingPath) {
            Storage::disk('s3')->delete($existingPath);
        }

        $path = $request->file('image')->store('covers', 's3');

        Cover::updateOrCreate(
            ['singleton' => true],
            ['image_url' => $path],
        );

        return back();
    }

    public function updateCover(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $existingCover = Cover::query()->latest()->first();
        $existingPath = $existingCover?->getRawOriginal('image_url');
        if ($existingPath) {
            Storage::disk('s3')->delete($existingPath);
        }

        $path = $request->file('image')->store('covers', 's3');

        Cover::updateOrCreate(
            ['singleton' => true],
            ['image_url' => $path],
        );

        return back();
    }

    public function addWish(Request $request)
    {
        if (! $request->user()) {
            return back()->withErrors([
                'auth' => 'Please log in to submit a wish.',
            ]);
        }

        $validated = $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        Wish::create([
            'user_id' => $request->user()->id,
            'message' => $validated['message'],
        ]);

        return back();
    }
}
