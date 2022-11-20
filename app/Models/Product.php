<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    public $fillabe = [
        'title',
        'description',
        'price'
    ];

    public function schedules(): HasMany
    {
        return $this->hasMany(ProductSchedule::class);
    }

    public function books(): HasMany
    {
        return $this->hasMany(ProductBook::class);
    }
}
