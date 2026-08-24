<?php

declare(strict_types=1);

namespace AndyDefer\LaravelRatings\Contracts\Services;

use AndyDefer\LaravelRatings\Enums\RatingLevel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use RuntimeException;

/**
 * Interface RatingServiceInterface
 *
 * Service interface for managing polymorphic ratings.
 * Provides methods for creating, updating, deleting, and querying ratings.
 */
interface RatingServiceInterface
{
    /**
     * Create a new rating.
     *
     * @param  Model  $rater  The entity creating the rating
     * @param  Model  $rateable  The entity being rated
     * @param  RatingLevel  $rating  The rating level (1-5 stars)
     * @param  string|null  $review  Optional review text
     * @return Model The created rating model
     *
     * @throws RuntimeException If the rater has already rated this rateable
     */
    public function rate(Model $rater, Model $rateable, RatingLevel $rating, ?string $review = null): Model;

    /**
     * Update an existing rating.
     *
     * @param  Model  $rater  The entity that created the rating
     * @param  Model  $rateable  The entity being rated
     * @param  RatingLevel  $rating  The new rating level (1-5 stars)
     * @param  string|null  $review  Optional new review text
     * @return Model The updated rating model
     *
     * @throws RuntimeException If the rater has not rated this rateable
     */
    public function updateRating(Model $rater, Model $rateable, RatingLevel $rating, ?string $review = null): Model;

    /**
     * Delete a rating.
     *
     * @param  Model  $rater  The entity that created the rating
     * @param  Model  $rateable  The entity being rated
     *
     * @throws RuntimeException If the rater has not rated this rateable
     */
    public function deleteRating(Model $rater, Model $rateable): void;

    /**
     * Check if a rater has rated a specific rateable.
     *
     * @param  Model  $rater  The entity that may have created a rating
     * @param  Model  $rateable  The entity being rated
     * @return bool True if the rater has rated the rateable
     */
    public function hasRated(Model $rater, Model $rateable): bool;

    /**
     * Get the rating from a specific rater for a specific rateable.
     *
     * @param  Model  $rater  The entity that created the rating
     * @param  Model  $rateable  The entity being rated
     * @return Model|null The rating model or null if not found
     */
    public function getRaterRating(Model $rater, Model $rateable): ?Model;

    /**
     * Get all ratings for a specific rateable.
     *
     * @param  Model  $rateable  The entity being rated
     * @param  RatingLevel|null  $minRating  Minimum rating level to filter (optional)
     * @param  RatingLevel|null  $maxRating  Maximum rating level to filter (optional)
     * @return Collection<int, Model> Collection of rating models
     */
    public function getRatings(Model $rateable, ?RatingLevel $minRating = null, ?RatingLevel $maxRating = null): Collection;

    /**
     * Calculate the average rating for a specific rateable.
     *
     * @param  Model  $rateable  The entity being rated
     * @return float The average rating (0.0 if no ratings)
     */
    public function getAverageRating(Model $rateable): float;

    /**
     * Get the distribution of ratings for a specific rateable.
     *
     * @param  Model  $rateable  The entity being rated
     * @return array<int, int> Array with rating levels as keys and counts as values
     */
    public function getRatingDistribution(Model $rateable): array;

    /**
     * Get all ratings created by a specific rater.
     *
     * @param  Model  $rater  The entity that created the ratings
     * @return Collection<int, Model> Collection of rating models
     */
    public function getRatingsByRater(Model $rater): Collection;

    /**
     * Count total ratings for a specific rateable.
     *
     * @param  Model  $rateable  The entity being rated
     * @return int Total number of ratings
     */
    public function countRatings(Model $rateable): int;

    /**
     * Count ratings for a specific rateable at a specific rating level.
     *
     * @param  Model  $rateable  The entity being rated
     * @param  RatingLevel  $level  The rating level to count (1-5 stars)
     * @return int Number of ratings at the specified level
     */
    public function countRatingsByLevel(Model $rateable, RatingLevel $level): int;
}
