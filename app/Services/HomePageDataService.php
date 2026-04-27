<?php

namespace App\Services;

use App\Models\Cover;
use App\Models\Gallery;
use App\Models\Journey;
use App\Models\Wish;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class HomePageDataService
{
    private const CACHE_VERSION_KEY = 'home_page_cache:version';
    private const CACHE_SCHEMA_VERSION = 2;
    private const TTL_MINUTES = 5;

    /**
     * Build all payload fields required by the welcome page.
     *
     * @return array<string, mixed>
     */
    public function buildPayload(): array
    {
        $version = $this->cacheVersion();
        $cachePrefix = 'home_page:s'.self::CACHE_SCHEMA_VERSION.":v{$version}";
        $journeys = Cache::remember(
            "{$cachePrefix}:journeys",
            now()->addMinutes(self::TTL_MINUTES),
            fn () => Journey::query()
                ->select(['id', 'title', 'story', 'image_url', 'journey_date', 'created_at'])
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
                ])
                ->values()
                ->all(),
        );

        $galleries = Cache::remember(
            "{$cachePrefix}:galleries:preview",
            now()->addMinutes(self::TTL_MINUTES),
            fn () => Gallery::query()
                ->select(['id', 'image_url', 'created_at'])
                ->latest()
                ->take(12)
                ->get()
                ->map(fn (Gallery $gallery) => [
                    'id' => $gallery->id,
                    'image' => $gallery->image_url,
                ])
                ->values()
                ->all(),
        );

        $coverImage = Cache::remember(
            "{$cachePrefix}:cover",
            now()->addMinutes(self::TTL_MINUTES),
            fn () => Cover::query()->latest()->value('image_url'),
        );
        $tones = ['bg-rose-100', 'bg-pink-100', 'bg-fuchsia-100', 'bg-rose-50'];

        $wishes = Cache::remember(
            "{$cachePrefix}:wishes:marquee",
            now()->addMinutes(self::TTL_MINUTES),
            fn () => Wish::query()
                ->select(['id', 'user_id', 'message', 'created_at'])
                ->latest()
                ->with('user:id,name')
                ->take(20)
                ->get()
                ->values()
                ->map(fn (Wish $wish, int $index) => [
                    'id' => $wish->id,
                    'name' => $wish->user?->name ?? 'Unknown',
                    'message' => $wish->message,
                    'tone' => $tones[$index % count($tones)],
                ])
                ->all(),
        );

        return [
            'journeyItems' => $journeys,
            'galleryItems' => $galleries,
            'galleryPages' => Inertia::scroll(function () {
                $paginator = Gallery::query()
                    ->select(['id', 'image_url', 'created_at'])
                    ->latest()
                    ->paginate(20);
                $paginator->setCollection(
                    $paginator->getCollection()->map(fn (Gallery $gallery) => [
                        'id' => $gallery->id,
                        'image' => $gallery->image_url,
                    ]),
                );

                return $paginator;
            }),
            'coverImage' => $coverImage,
            'wishes' => $wishes,
            'wishPages' => Inertia::scroll(function () use ($tones) {
                $paginator = Wish::query()
                    ->select(['id', 'user_id', 'message', 'created_at'])
                    ->latest()
                    ->with('user:id,name')
                    ->paginate(20);
                $paginator->setCollection(
                    $paginator->getCollection()->values()->map(fn (Wish $wish, int $index) => [
                        'id' => $wish->id,
                        'name' => $wish->user?->name ?? 'Unknown',
                        'message' => $wish->message,
                        'tone' => $tones[$index % count($tones)],
                    ]),
                );

                return $paginator;
            }),
        ];
    }

    public function clearHomePageCache(): void
    {
        Cache::forever(self::CACHE_VERSION_KEY, $this->cacheVersion() + 1);
    }

    private function cacheVersion(): int
    {
        return (int) Cache::rememberForever(self::CACHE_VERSION_KEY, fn () => 1);
    }
}
