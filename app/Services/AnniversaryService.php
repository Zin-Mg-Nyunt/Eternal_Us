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
        
        $anniversaryBanner = null;
        if ($now->isBirthday($dates["marriageDate"])) {
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
        
        $marriageLabel = $marriageDuration->y." နှစ်ပြည့်";
        $relationshipLabel = $relDuration->y." နှစ် ".($relDuration->m > 0 ? "နှင့် {$relDuration->m} လပြည့်" : "ပြည့်");
        
        return [
            'marriage' => [
                'years' => $marriageDuration->y,
                'months' => $marriageDuration->m,
                'days' => $marriageDuration->d,
                'label' => $marriageLabel,
                'dateFormat' => $dates["marriageDate"]->format('d M Y'),
            ],
            'relationship' => [
                'years' => $relDuration->y,
                'months' => $relDuration->m,
                'days' => $relDuration->d,
                'label' => $relationshipLabel,
                'dateFormat' => $dates["relationshipStartDate"]->format('d M Y'),
            ],
            'banner' => $anniversaryBanner,
            'showBanner' => $anniversaryBanner !== null,
        ];
    }
}