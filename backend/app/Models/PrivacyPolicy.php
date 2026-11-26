<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PrivacyPolicy extends Model
{
    protected $fillable = [
        'version',
        'title',
        'content',
        'is_current',
        'is_enabled',
    ];

    protected function casts(): array
    {
        return [
            'is_current' => 'boolean',
            'is_enabled' => 'boolean',
        ];
    }

    public function scopeCurrent(Builder $query): Builder
    {
        return $query->where('is_current', true);
    }

    public function acceptances(): HasMany
    {
        return $this->hasMany(PrivacyPolicyAcceptance::class);
    }
}
