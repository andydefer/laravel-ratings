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
 *     'raterType' => User::class,
 *     'raterId' => 123,
 *     'rateableType' => Product::class,
 *     'rateableId' => 456,
 *     'ratingLevel' => RatingLevel::FIVE,
 *     'review' => 'Excellent product!',
 *     'metadata' => ['order_id' => 789],
 *     'createdAt' => '2024-01-15 10:00:00',
 *     'updatedAt' => '2024-01-15 10:00:00',
 * ]);
 */
final class RatingData extends AbstractData
{
    use Hydratable;

    public function __construct(
        public readonly ?int $id,
        public readonly string $raterType,
        public readonly int $raterId,
        public readonly string $rateableType,
        public readonly int $rateableId,
        public readonly RatingLevel $ratingLevel,
        public readonly ?string $review,
        public readonly ?StrictDataObject $metadata,
        public readonly ?DateTimeVO $createdAt,
        public readonly ?DateTimeVO $updatedAt,
        public readonly ?DateTimeVO $deletedAt,
    ) {}
}
