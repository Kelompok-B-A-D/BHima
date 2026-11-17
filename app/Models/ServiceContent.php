<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ServiceContent extends Model
{
    protected $table = 'dashboard_records';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['payload' => 'array'];
    }

    protected static function booted(): void
    {
        static::addGlobalScope('contents', fn (Builder $query) => $query->where('collection', 'contents'));
    }

    public function scopePublished(Builder $query): void
    {
        $query->where('payload->status', 'Terbit');
    }
}
