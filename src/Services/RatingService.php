<?php

declare(strict_types=1);

namespace AndyDefer\LaravelRatings\Services;

use AndyDefer\LaravelRatings\Enums\RatingLevel;
use AndyDefer\LaravelRatings\Records\RatingFilterRecord;
use AndyDefer\LaravelRatings\Records\RatingRecord;
use AndyDefer\LaravelRatings\Repositories\RatingRepository;
use AndyDefer\Repository\Records\FindByRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use RuntimeException;

final class RatingService
{
    public function __construct(
        private readonly RatingRepository $ratingRepository,
    ) {}

    public function rate(Model $rater, Model $rateable, RatingLevel $rating, ?string $review = null): Model
    {
        $existing = $this->findExisting($rater, $rateable);

        if ($existing) {
            throw new RuntimeException(sprintf(
                '%s %s has already rated %s %s',
                $rater->getMorphClass(),
                $rater->getKey(),
                $rateable->getMorphClass(),
                $rateable->getKey()
            ));
        }

        $record = RatingRecord::from([
            'rater_type' => $rater->getMorphClass(),
            'rater_id' => $rater->getKey(),
            'rateable_type' => $rateable->getMorphClass(),
            'rateable_id' => $rateable->getKey(),
            'rating_level' => $rating,
            'review' => $review,
        ]);

        return $this->ratingRepository->create($record);
    }

    public function updateRating(Model $rater, Model $rateable, RatingLevel $rating, ?string $review = null): Model
    {
        $existing = $this->findExisting($rater, $rateable);

        if (! $existing) {
            throw new RuntimeException(sprintf(
                '%s %s has not rated %s %s',
                $rater->getMorphClass(),
                $rater->getKey(),
                $rateable->getMorphClass(),
                $rateable->getKey()
            ));
        }

        $updateRecord = RatingRecord::from([
            'rating_level' => $rating,
            'review' => $review,
        ]);

        return $this->ratingRepository->update($existing->id, $updateRecord);
    }

    public function deleteRating(Model $rater, Model $rateable): void
    {
        $existing = $this->findExisting($rater, $rateable);

        if (! $existing) {
            throw new RuntimeException(sprintf(
                '%s %s has not rated %s %s',
                $rater->getMorphClass(),
                $rater->getKey(),
                $rateable->getMorphClass(),
                $rateable->getKey()
            ));
        }

        $this->ratingRepository->delete($existing->id);
    }

    private function findExisting(Model $rater, Model $rateable): ?Model
    {
        $filter = RatingFilterRecord::from([
            'rater_type' => $rater->getMorphClass(),
            'rater_id' => $rater->getKey(),
            'rateable_type' => $rateable->getMorphClass(),
            'rateable_id' => $rateable->getKey(),
        ]);

        $findByRecord = new FindByRecord(
            filters: $filter,
            limit: 1,
        );

        $collection = $this->ratingRepository->findBy($findByRecord);

        return $collection->first();
    }

    public function hasRated(Model $rater, Model $rateable): bool
    {
        $filter = RatingFilterRecord::from([
            'rater_type' => $rater->getMorphClass(),
            'rater_id' => $rater->getKey(),
            'rateable_type' => $rateable->getMorphClass(),
            'rateable_id' => $rateable->getKey(),
        ]);

        return $this->ratingRepository->exists($filter);
    }

    public function getRaterRating(Model $rater, Model $rateable): ?Model
    {
        $filter = RatingFilterRecord::from([
            'rater_type' => $rater->getMorphClass(),
            'rater_id' => $rater->getKey(),
            'rateable_type' => $rateable->getMorphClass(),
            'rateable_id' => $rateable->getKey(),
        ]);

        $findByRecord = new FindByRecord(
            filters: $filter,
            limit: 1,
        );

        $collection = $this->ratingRepository->findBy($findByRecord);

        return $collection->first();
    }

    public function getRatings(Model $rateable, ?RatingLevel $minRating = null, ?RatingLevel $maxRating = null): Collection
    {
        $filter = RatingFilterRecord::from([
            'rateable_type' => $rateable->getMorphClass(),
            'rateable_id' => $rateable->getKey(),
            'min_rating' => $minRating,
            'max_rating' => $maxRating,
        ]);

        $findByRecord = new FindByRecord(filters: $filter);

        return $this->ratingRepository->findBy($findByRecord);
    }

    public function getAverageRating(Model $rateable): float
    {
        return $this->ratingRepository->getAverageRating($rateable);
    }

    public function getRatingDistribution(Model $rateable): array
    {
        return $this->ratingRepository->getRatingDistribution($rateable);
    }

    public function getRatingsByRater(Model $rater): Collection
    {
        $filter = RatingFilterRecord::from([
            'rater_type' => $rater->getMorphClass(),
            'rater_id' => $rater->getKey(),
        ]);

        $findByRecord = new FindByRecord(filters: $filter);

        return $this->ratingRepository->findBy($findByRecord);
    }

    public function countRatings(Model $rateable): int
    {
        $filter = RatingFilterRecord::from([
            'rateable_type' => $rateable->getMorphClass(),
            'rateable_id' => $rateable->getKey(),
        ]);

        return $this->ratingRepository->count($filter);
    }

    public function countRatingsByLevel(Model $rateable, RatingLevel $level): int
    {
        $filter = RatingFilterRecord::from([
            'rateable_type' => $rateable->getMorphClass(),
            'rateable_id' => $rateable->getKey(),
            'rating_level' => $level,
        ]);

        return $this->ratingRepository->count($filter);
    }
}
