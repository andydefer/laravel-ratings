<?php

declare(strict_types=1);

namespace AndyDefer\LaravelRatings\Repositories;

use AndyDefer\DomainStructures\Abstracts\AbstractRecord;
use AndyDefer\LaravelRatings\Enums\RatingLevel;
use AndyDefer\LaravelRatings\Models\Rating;
use AndyDefer\LaravelRatings\Records\RatingFilterRecord;
use AndyDefer\LaravelRatings\Records\RatingRecord;
use AndyDefer\Repository\AbstractRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

final class RatingRepository extends AbstractRepository
{
    public function __construct()
    {
        parent::__construct(
            modelClass: Rating::class,
            recordClass: RatingRecord::class,
        );
    }

    public function getAverageRating(Model $rateable): float
    {
        $filter = RatingFilterRecord::from([
            'rateable_type' => $rateable->getMorphClass(),
            'rateable_id' => $rateable->getKey(),
        ]);

        $query = $this->buildQuery($filter);
        $avg = $query->avg('rating_level');

        return round($avg ?? 0, 2);
    }

    public function getRatingDistribution(Model $rateable): array
    {
        $filter = RatingFilterRecord::from([
            'rateable_type' => $rateable->getMorphClass(),
            'rateable_id' => $rateable->getKey(),
        ]);

        $query = $this->buildQuery($filter);
        $distribution = [];

        foreach (RatingLevel::cases() as $level) {
            $distribution[$level->value] = (clone $query)->where('rating_level', $level->value)->count();
        }

        return $distribution;
    }

    protected function applyFilters(Builder $query, AbstractRecord $filters): void
    {
        if (! $filters instanceof RatingFilterRecord) {
            return;
        }

        if ($filters->rater_type !== null) {
            $query->where('rater_type', $filters->rater_type);
        }

        if ($filters->rater_id !== null) {
            $query->where('rater_id', $filters->rater_id);
        }

        if ($filters->rateable_type !== null) {
            $query->where('rateable_type', $filters->rateable_type);
        }

        if ($filters->rateable_id !== null) {
            $query->where('rateable_id', $filters->rateable_id);
        }

        if ($filters->rating_level !== null) {
            $query->where('rating_level', $filters->rating_level->value);
        }

        if ($filters->min_rating !== null) {
            $query->where('rating_level', '>=', $filters->min_rating->value);
        }

        if ($filters->max_rating !== null) {
            $query->where('rating_level', '<=', $filters->max_rating->value);
        }
    }
}
