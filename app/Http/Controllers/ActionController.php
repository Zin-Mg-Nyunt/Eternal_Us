<?php

namespace App\Http\Controllers;

use App\Services\HomePageDataService;
use App\Services\MediaManagerService;
use App\Services\WishService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ActionController extends Controller
{
    public function __construct(
        private readonly HomePageDataService $homePageDataService,
        private readonly MediaManagerService $mediaManagerService,
        private readonly WishService $wishService,
    ) {}

    public function home(): Response
    {
        return Inertia::render('Welcome', $this->homePageDataService->buildPayload());
    }

    public function addJourney(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'story' => 'required|string|max:2000',
            'journey_date' => 'required|date',
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $this->mediaManagerService->createJourney($validated, $request->file('image'));
        $this->homePageDataService->clearHomePageCache();

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

        $this->mediaManagerService->updateJourney($validated, $request->file('image'));
        $this->homePageDataService->clearHomePageCache();

        return back();
    }

    public function addGallery(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $this->mediaManagerService->createGallery($request->file('image'));
        $this->homePageDataService->clearHomePageCache();

        return back();
    }

    public function updateGallery(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|integer|exists:galleries,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $this->mediaManagerService->updateGallery($validated, $request->file('image'));
        $this->homePageDataService->clearHomePageCache();

        return back();
    }

    public function addCover(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $this->mediaManagerService->upsertCover($request->file('image'));
        $this->homePageDataService->clearHomePageCache();

        return back();
    }

    public function updateCover(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $this->mediaManagerService->upsertCover($request->file('image'));
        $this->homePageDataService->clearHomePageCache();

        return back();
    }

    public function addWish(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $this->wishService->createForUser($request->user(), $validated['message']);
        $this->homePageDataService->clearHomePageCache();

        return back();
    }
}
