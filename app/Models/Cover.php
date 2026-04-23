<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Cover extends Model
{
    protected $fillable = [
        'image_url',
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
