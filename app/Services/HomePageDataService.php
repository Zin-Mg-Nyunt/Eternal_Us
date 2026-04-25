<?php

namespace App\Services;

use App\Models\Cover;
use App\Models\Gallery;
use App\Models\Journey;
use App\Models\Wish;
use Inertia\Inertia;

class HomePageDataService
{
    /**
     * Build all payload fields required by the welcome page.
     *
     * @return array<string, mixed>
     */
    public function buildPayload(): array
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

        return [
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
        ];
    }
}
