<?php

namespace App\Services;

use Carbon\Carbon;

class AnniversaryService
{
    private static function getDates(){
        return [
            "relationshipStartDate" => Carbon::parse('2020-08-29'),
            "marriageDate" => Carbon::parse('2026-03-22'),
            "now" => Carbon::now(),
        ];
    }

    public static function getAnniversary()
    {
        $dates = self::getDates();
        $now = $dates["now"];

        $relDuration = $now->diff($dates["relationshipStartDate"]);
        $marriageDuration = $now->diff($dates["marriageDate"]);
        
        $marriageYears = $marriageDuration->y;
        $marriageMonths = $marriageDuration->m;
        $marriageDays = $marriageDuration->d;
        
        
        $relationshipYears = $relDuration->y;
        $relationshipMonths = $relDuration->m;
        $relationshipDays = $relDuration->d;

        $anniversaryBanner = null;
        if ($now->isSameDay($dates["marriageDate"])) {
            $anniversaryBanner = [
                'type' => 'marriage',
                'message' => 'ပျော်ရွှင်စရာ မင်္ဂလာနှစ်ပတ်လည်နေ့ပါ သဲငယ်လေးရေ',
            ];
        } elseif ($now->day === $dates["relationshipStartDate"]->day) {
            $anniversaryBanner = [
                'type' => 'relationship',
                'message' => 'ပျော်ရွှင်စရာ ချစ်သူနှစ်ပတ်လည်နေ့ပါ သဲငယ်လေးရေ!',
            ];
        }
        
        $marriageLabel = $marriageYears." နှစ်ပြည့်";
        $relationshipLabel = $relationshipYears." နှစ် ".($relationshipMonths > 0 ? "နှင့် {$relationshipMonths} လပြည့်" : "ပြည့်");
        
        return [
            'marriage' => [
                'years' => $marriageYears,
                'months' => $marriageMonths,
                'days' => $marriageDays,
                'label' => $marriageLabel,
                'dateFormat' => $dates["marriageDate"]->format('d M Y'),
            ],
            'relationship' => [
                'years' => $relationshipYears,
                'months' => $relationshipMonths,
                'days' => $relationshipDays,
                'label' => $relationshipLabel,
                'dateFormat' => $dates["relationshipStartDate"]->format('d M Y'),
            ],
            'banner' => $anniversaryBanner,
            'showBanner' => $anniversaryBanner !== null,
        ];
    }
}