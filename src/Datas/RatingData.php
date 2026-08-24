<?php

declare(strict_types=1);

namespace AndyDefer\LaravelRatings\Datas;

use AndyDefer\DomainStructures\Abstracts\AbstractData;
use AndyDefer\DomainStructures\Traits\Hydratable;
use AndyDefer\DomainStructures\Utils\StrictDataObject;
use AndyDefer\LaravelRatings\Enums\RatingLevel;
use AndyDefer\PhpVo\ValueObjects\DateTimeVO;

/**
 * Data Transfer Object for Rating.
 *
 * This DTO is used for API responses and data transfer between layers.
 * It provides a clean, typed representation of a Rating with all its properties.
 *
 * @example
 * $ratingData = RatingData::from([
 *     'id' => 1,
 *     'rater_type' => User::class,
 *     'rater_id' => 123,
 *     'rateable_type' => Product::class,
 *     'rateable_id' => 456,
 *     'rating_level' => RatingLevel::FIVE,
 *     'review' => 'Excellent product!',
 *     'metadata' => ['order_id' => 789],
 *     'created_at' => '2024-01-15 10:00:00',
 *     'updated_at' => '2024-01-15 10:00:00',
 * ]);
 */
final class RatingData extends AbstractData
{
    use Hydratable;

    public function __construct(
        public readonly ?int $id,
        public readonly string $rater_type,
        public readonly int $rater_id,
        public readonly string $rateable_type,
        public readonly int $rateable_id,
        public readonly RatingLevel $rating_level,
        public readonly ?string $review,
        public readonly ?StrictDataObject $metadata,
        public readonly ?DateTimeVO $created_at,
        public readonly ?DateTimeVO $updated_at,
        public readonly ?DateTimeVO $deleted_at,
    ) {}
}
