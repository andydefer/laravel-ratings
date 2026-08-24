<?php

declare(strict_types=1);

namespace AndyDefer\LaravelRatings\Models;

use AndyDefer\DomainStructures\Utils\StrictDataObject;
use AndyDefer\LaravelRatings\Enums\RatingLevel;
use AndyDefer\Repository\Proxies\AttributeProxy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Rating model representing a polymorphic rating.
 *
 * @property int $id
 * @property string $rater_type
 * @property int $rater_id
 * @property string $rateable_type
 * @property int $rateable_id
 * @property RatingLevel $rating_level
 * @property string|null $review
 * @property StrictDataObject|null $metadata
 * @property-read Model|null $rater
 * @property-read Model|null $rateable
 */
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

    /**
     * Get the rater (the entity that created the rating).
     */
    public function rater(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the rateable (the entity being rated).
     */
    public function rateable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the metadata attribute as a StrictDataObject.
     */
    protected function metadata(): Attribute
    {
        return AttributeProxy::nullable(
            StrictDataObject::class,
            column: 'metadata'
        );
    }
}
