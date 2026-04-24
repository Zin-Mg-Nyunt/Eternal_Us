<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $relationshipStartDate = Carbon::parse('2020-08-29');
        $marriageDate = Carbon::parse('2026-03-22');
        $now = Carbon::now();

        $relationshipDiff = $now->diff($relationshipStartDate);
        $marriageDiff = $now->diff($marriageDate);
        
        $marriageYears = $marriageDiff->y;
        $marriageMonths = $marriageDiff->m;
        $marriageDays = $marriageDiff->d;
        
        
        $relationshipYears = $relationshipDiff->y;
        $relationshipMonths = $relationshipDiff->m;
        $relationshipDays = $relationshipDiff->d;

        $isMarriageAnniversary = $now->month === $marriageDate->month
            && $now->day === $marriageDate->day;
        $isRelationshipAnniversary = $now->day === $relationshipStartDate->day;

        $anniversaryBanner = null;
        if ($isMarriageAnniversary) {
            $anniversaryBanner = [
                'type' => 'marriage',
                'message' => 'ပျော်ရွှင်စရာ မင်္ဂလာနှစ်ပတ်လည်နေ့ပါ သဲငယ်လေးရေ',
            ];
        } elseif ($isRelationshipAnniversary) {
            $anniversaryBanner = [
                'type' => 'relationship',
                'message' => 'ပျော်ရွှင်စရာ ချစ်သူနှစ်ပတ်လည်နေ့ပါ သဲငယ်လေးရေ!',
            ];
        }

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $request->user(),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'anniversary' => [
                'marriage' => [
                    'years' => $marriageYears,
                    'months' => $marriageMonths,
                    'days' => $marriageDays,
                    'dateFormat' => $marriageDate->format('d M Y'),
                ],
                'relationship' => [
                    'years' => $relationshipYears,
                    'months' => $relationshipMonths,
                    'days' => $relationshipDays,
                    'dateFormat' => $relationshipStartDate->format('d M Y'),
                ],
                'marriageLabel' => "{$marriageYears} နှစ်ပြည့်",
                'relationshipLabel' => $relationshipYears." နှစ် ".($relationshipMonths > 0 ? "နှင့် {$relationshipMonths} လပြည့်" : "ပြည့်"),
                'showBanner' => $anniversaryBanner !== null,
                'banner' => $anniversaryBanner,
            ],
        ];
    }
}
