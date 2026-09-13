<?php

// vendor/andydefer/laravel-ratings/src/Records/RatingFilterRecord.php (ou copie locale)

declare(strict_types=1);

namespace AndyDefer\LaravelRatings\Records;

use AndyDefer\DomainStructures\Abstracts\AbstractRecord;
use AndyDefer\LaravelRatings\Enums\RatingLevel;

final class RatingFilterRecord extends AbstractRecord
{
    public function __construct(
        public readonly ?string $rater_type = null,
        public readonly ?string $rater_id = null,
        public readonly ?string $rateable_type = null,
        public readonly ?string $rateable_id = null,
        public readonly ?RatingLevel $rating_level = null,
        public readonly ?RatingLevel $min_rating = null,
        public readonly ?RatingLevel $max_rating = null,
    ) {}
}
