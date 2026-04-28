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
    /**
     * @var array{items: array<int, array<string, mixed>>, pagination: array<string, int|null>}|null
     */
    private ?array $journeyPayloadForRequest = null;

    private const CACHE_VERSION_KEY = 'home_page_cache:version';

    private const CACHE_SCHEMA_VERSION = 2;

    private const TTL_MINUTES = 5;

    /**
     * Welcome page props. Values are closures so Inertia partial reloads
     * only resolve the props requested by the client (`only` / `except`).
     *
     * @return array<string, mixed>
     */
    public function buildPayload(): array
    {
        return [
            'journeyItems' => fn () => $this->buildJourney()['items'],
            'journeyPagination' => fn () => $this->buildJourney()['pagination'],
            'galleryItems' => fn () => $this->buildGalleryPreviewItems(),
            'galleryPages' => Inertia::scroll(fn () => $this->buildGalleryPaginator()),
            'coverImage' => fn () => $this->buildCoverImage(),
            'wishes' => fn () => $this->buildWishesMarquee(),
            'wishPages' => Inertia::scroll(fn () => $this->buildWishPagesPaginator()),
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

    private function cachePrefix(): string
    {
        return 'home_page:s'.self::CACHE_SCHEMA_VERSION.':v'.$this->cacheVersion();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildGalleryPreviewItems(): array
    {
        $prefix = $this->cachePrefix();

        return Cache::remember(
            "{$prefix}:galleries:preview",
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
    }

    private function buildCoverImage(): ?string
    {
        $prefix = $this->cachePrefix();

        return Cache::remember(
            "{$prefix}:cover",
            now()->addMinutes(self::TTL_MINUTES),
            fn () => Cover::query()->latest()->value('image_url'),
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildWishesMarquee(): array
    {
        $prefix = $this->cachePrefix();
        $tones = ['bg-rose-100', 'bg-pink-100', 'bg-fuchsia-100', 'bg-rose-50'];

        return Cache::remember(
            "{$prefix}:wishes:marquee",
            now()->addMinutes(self::TTL_MINUTES),
            fn () => Wish::query()
                ->select(['id', 'user_id', 'message', 'created_at'])
                ->latest()
                ->with('user:id,name')
                ->take(20)
                ->get()
                ->map(fn (Wish $wish, int $index) => [
                    'id' => $wish->id,
                    'name' => $wish->user?->name ?? 'Unknown',
                    'message' => $wish->message,
                    'tone' => $tones[$index % count($tones)],
                ])
                ->values()
                ->all(),
        );
    }

    private function buildGalleryPaginator()
    {
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
    }

    private function buildWishPagesPaginator()
    {
        $tones = ['bg-rose-100', 'bg-pink-100', 'bg-fuchsia-100', 'bg-rose-50'];
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
    }

    private function buildJourney()
    {
        if ($this->journeyPayloadForRequest !== null) {
            return $this->journeyPayloadForRequest;
        }

        $prefix = $this->cachePrefix();
        $page = max(1, (int) request()->query('journey_page', 1));

        $this->journeyPayloadForRequest = Cache::remember(
            "{$prefix}:journeys:page:{$page}",
            now()->addMinutes(self::TTL_MINUTES),
            function () use ($page) {
                $paginator = Journey::query()
                    ->select(['id', 'title', 'story', 'image_url', 'journey_date', 'created_at'])
                    ->orderBy('journey_date', 'asc')
                    ->orderBy('id', 'asc')
                    ->paginate(4, ['*'], 'journey_page', $page);

                $items = $paginator->getCollection()
                    ->map(fn (Journey $journey) => [
                        'id' => $journey->id,
                        'title' => $journey->title,
                        'story' => $journey->story,
                        'description' => $journey->story,
                        'journeyDate' => optional($journey->journey_date)->format('Y-m-d'),
                        'date' => optional($journey->journey_date)->format('Y-m-d')
                                ?? optional($journey->created_at)->format('Y-m-d')
                                ?? 'Today',
                        'image' => $journey->image_url,
                    ])
                    ->values()
                    ->all();

                $pagination = [
                    'currentPage' => $paginator->currentPage(),
                    'lastPage' => $paginator->lastPage(),
                    'prevPage' => $paginator->currentPage() > 1
                        ? $paginator->currentPage() - 1
                        : null,
                    'nextPage' => $paginator->hasMorePages()
                        ? $paginator->currentPage() + 1
                        : null,
                ];

                return compact('items', 'pagination');
            },
        );
        return $this->journeyPayloadForRequest;
    }
}
