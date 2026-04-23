<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Journey extends Model
{
    protected $fillable = [
        'title',
        'story',
        'image_url',
        'journey_date',
    ];

    protected $casts = [
        'journey_date' => 'date',
    ];

    protected function imageUrl(): Attribute
    {
        return Attribute::make(
            get: static fn (?string $value) => blank($value)
                ? null
                : (str_starts_with($value, 'http://') ||
                   str_starts_with($value, 'https://')
                    ? $value
                    : Storage::url($value)),
        );
    }
}
