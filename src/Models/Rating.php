<?php

declare(strict_types=1);

namespace AndyDefer\LaravelRatings\Models;

use AndyDefer\DomainStructures\Utils\StrictDataObject;
use AndyDefer\LaravelRatings\Enums\RatingLevel;
use AndyDefer\PhpVo\ValueObjects\DateTimeVO;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Rating extends Model
{
    use SoftDeletes;

    protected $table = 'ratings';

    protected $fillable = [
        'rater_type',
        'rater_id',
        'rateable_type',
        'rateable_id',
        'rating_level',
        'review',
        'metadata',
    ];

    protected $casts = [
        'rating_level' => RatingLevel::class,
        'metadata' => 'array',
    ];

    public function rater()
    {
        return $this->morphTo();
    }

    public function rateable()
    {
        return $this->morphTo();
    }

    public function getCreatedAt(): ?DateTimeVO
    {
        return $this->created_at ? DateTimeVO::from($this->created_at) : null;
    }

    public function getUpdatedAt(): ?DateTimeVO
    {
        return $this->updated_at ? DateTimeVO::from($this->updated_at) : null;
    }

    public function getDeletedAt(): ?DateTimeVO
    {
        return $this->deleted_at ? DateTimeVO::from($this->deleted_at) : null;
    }

    public function getMetadata(): ?StrictDataObject
    {
        return $this->metadata ? StrictDataObject::from($this->metadata) : null;
    }

    public function getRatingLevel(): RatingLevel
    {
        return $this->rating_level;
    }

    public function getReview(): ?string
    {
        return $this->review;
    }
}
