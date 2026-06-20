<?php

declare(strict_types=1);

namespace AndyDefer\LaravelRatings\Records;

use AndyDefer\DomainStructures\Abstracts\AbstractRecord;
use AndyDefer\DomainStructures\Utils\StrictDataObject;
use AndyDefer\LaravelRatings\Enums\RatingLevel;
use AndyDefer\PhpVo\ValueObjects\DateTimeVO;

final class RatingRecord extends AbstractRecord
{
    public function __construct(
        public readonly ?int $id = null,
        public readonly ?string $rater_type = null,
        public readonly ?int $rater_id = null,
        public readonly ?string $rateable_type = null,
        public readonly ?int $rateable_id = null,
        public readonly ?RatingLevel $rating_level = null,
        public readonly ?string $review = null,
        public readonly ?StrictDataObject $metadata = null,
        public readonly ?DateTimeVO $created_at = null,
        public readonly ?DateTimeVO $updated_at = null,
        public readonly ?DateTimeVO $deleted_at = null,
    ) {}
}
