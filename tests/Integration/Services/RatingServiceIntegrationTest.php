<?php

declare(strict_types=1);

namespace AndyDefer\LaravelFeatures\Tests\Integration\Ratings\Services;

use AndyDefer\LaravelRatings\Enums\RatingLevel;
use AndyDefer\LaravelRatings\Models\Rating;
use AndyDefer\LaravelRatings\Repositories\RatingRepository;
use AndyDefer\LaravelRatings\Services\RatingService;
use AndyDefer\LaravelRatings\Tests\Fixtures\Models\TestPost;
use AndyDefer\LaravelRatings\Tests\Fixtures\Models\TestUser;
use AndyDefer\LaravelRatings\Tests\IntegrationTestCase;
use RuntimeException;

final class RatingServiceIntegrationTest extends IntegrationTestCase
{
    private RatingService $ratingService;

    private TestUser $user;

    private TestPost $post;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ratingService = new RatingService(
            new RatingRepository
        );

        $this->user = TestUser::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $this->post = TestPost::create([
            'user_id' => $this->user->id,
            'title' => 'Test Post',
            'body' => 'Test content',
        ]);
    }

    public function test_rate_creates_rating_when_not_exists(): void
    {
        $rating = $this->ratingService->rate($this->user, $this->post, RatingLevel::FIVE, 'Excellent post!');

        $this->assertInstanceOf(Rating::class, $rating);
        $this->assertSame(RatingLevel::FIVE, $rating->rating_level);
        $this->assertSame('Excellent post!', $rating->review);
        $this->assertTrue($this->ratingService->hasRated($this->user, $this->post));
    }

    public function test_rate_throws_exception_when_already_rated(): void
    {
        $this->ratingService->rate($this->user, $this->post, RatingLevel::FIVE);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('has already rated');

        $this->ratingService->rate($this->user, $this->post, RatingLevel::FOUR);
    }

    public function test_update_rating_modifies_existing_rating(): void
    {
        $this->ratingService->rate($this->user, $this->post, RatingLevel::THREE, 'Not bad');

        $updated = $this->ratingService->updateRating($this->user, $this->post, RatingLevel::FIVE, 'Excellent!');

        $this->assertSame(RatingLevel::FIVE, $updated->rating_level);
        $this->assertSame('Excellent!', $updated->review);
    }

    public function test_update_rating_throws_exception_when_not_rated(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('has not rated');

        $this->ratingService->updateRating($this->user, $this->post, RatingLevel::FIVE);
    }

    public function test_delete_rating_removes_rating(): void
    {
        $this->ratingService->rate($this->user, $this->post, RatingLevel::FIVE);

        $this->assertTrue($this->ratingService->hasRated($this->user, $this->post));

        $this->ratingService->deleteRating($this->user, $this->post);

        $this->assertFalse($this->ratingService->hasRated($this->user, $this->post));
    }

    public function test_delete_rating_throws_exception_when_not_rated(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('has not rated');

        $this->ratingService->deleteRating($this->user, $this->post);
    }

    public function test_has_rated_returns_false_when_not_rated(): void
    {
        $result = $this->ratingService->hasRated($this->user, $this->post);

        $this->assertFalse($result);
    }

    public function test_has_rated_returns_true_when_rated(): void
    {
        $this->ratingService->rate($this->user, $this->post, RatingLevel::FIVE);

        $result = $this->ratingService->hasRated($this->user, $this->post);

        $this->assertTrue($result);
    }

    public function test_get_rater_rating_returns_rating(): void
    {
        $this->ratingService->rate($this->user, $this->post, RatingLevel::FOUR, 'Good post');

        $rating = $this->ratingService->getRaterRating($this->user, $this->post);

        $this->assertNotNull($rating);
        $this->assertSame(RatingLevel::FOUR, $rating->rating_level);
        $this->assertSame('Good post', $rating->review);
    }

    public function test_get_rater_rating_returns_null_when_not_rated(): void
    {
        $rating = $this->ratingService->getRaterRating($this->user, $this->post);

        $this->assertNull($rating);
    }

    public function test_get_ratings_returns_all_ratings_for_rateable(): void
    {
        $user2 = TestUser::create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ]);

        $this->ratingService->rate($this->user, $this->post, RatingLevel::FIVE);
        $this->ratingService->rate($user2, $this->post, RatingLevel::FOUR);

        $ratings = $this->ratingService->getRatings($this->post);

        $this->assertCount(2, $ratings);
    }

    public function test_get_ratings_with_min_rating_filters_correctly(): void
    {
        $user2 = TestUser::create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ]);

        $this->ratingService->rate($this->user, $this->post, RatingLevel::FIVE);
        $this->ratingService->rate($user2, $this->post, RatingLevel::THREE);

        $ratings = $this->ratingService->getRatings($this->post, RatingLevel::FOUR);

        $this->assertCount(1, $ratings);
        $this->assertSame(RatingLevel::FIVE, $ratings->first()->rating_level);
    }

    public function test_get_ratings_with_max_rating_filters_correctly(): void
    {
        $user2 = TestUser::create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ]);

        $this->ratingService->rate($this->user, $this->post, RatingLevel::FIVE);
        $this->ratingService->rate($user2, $this->post, RatingLevel::THREE);

        $ratings = $this->ratingService->getRatings($this->post, maxRating: RatingLevel::THREE);

        $this->assertCount(1, $ratings);
        $this->assertSame(RatingLevel::THREE, $ratings->first()->rating_level);
    }

    public function test_get_average_rating_returns_correct_average(): void
    {
        $user2 = TestUser::create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ]);
        $user3 = TestUser::create([
            'name' => 'Bob Smith',
            'email' => 'bob@example.com',
        ]);

        $this->ratingService->rate($this->user, $this->post, RatingLevel::FIVE);
        $this->ratingService->rate($user2, $this->post, RatingLevel::FOUR);
        $this->ratingService->rate($user3, $this->post, RatingLevel::THREE);

        $average = $this->ratingService->getAverageRating($this->post);

        $this->assertSame(4.0, $average);
    }

    public function test_get_average_rating_returns_zero_when_no_ratings(): void
    {
        $average = $this->ratingService->getAverageRating($this->post);

        $this->assertSame(0.0, $average);
    }

    public function test_get_rating_distribution_returns_correct_distribution(): void
    {
        $user2 = TestUser::create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ]);
        $user3 = TestUser::create([
            'name' => 'Bob Smith',
            'email' => 'bob@example.com',
        ]);

        $this->ratingService->rate($this->user, $this->post, RatingLevel::FIVE);
        $this->ratingService->rate($user2, $this->post, RatingLevel::FIVE);
        $this->ratingService->rate($user3, $this->post, RatingLevel::THREE);

        $distribution = $this->ratingService->getRatingDistribution($this->post);

        $this->assertSame(0, $distribution[1]);
        $this->assertSame(0, $distribution[2]);
        $this->assertSame(1, $distribution[3]);
        $this->assertSame(0, $distribution[4]);
        $this->assertSame(2, $distribution[5]);
    }

    public function test_get_ratings_by_rater_returns_all_ratings_from_rater(): void
    {
        $post2 = TestPost::create([
            'user_id' => $this->user->id,
            'title' => 'Second Post',
            'body' => 'Another content',
        ]);

        $this->ratingService->rate($this->user, $this->post, RatingLevel::FIVE);
        $this->ratingService->rate($this->user, $post2, RatingLevel::FOUR);

        $ratings = $this->ratingService->getRatingsByRater($this->user);

        $this->assertCount(2, $ratings);
    }

    public function test_count_ratings_returns_correct_number(): void
    {
        $user2 = TestUser::create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ]);

        $this->ratingService->rate($this->user, $this->post, RatingLevel::FIVE);
        $this->ratingService->rate($user2, $this->post, RatingLevel::FOUR);

        $count = $this->ratingService->countRatings($this->post);

        $this->assertSame(2, $count);
    }

    public function test_count_ratings_returns_zero_when_no_ratings(): void
    {
        $count = $this->ratingService->countRatings($this->post);

        $this->assertSame(0, $count);
    }

    public function test_count_ratings_by_level_returns_correct_number(): void
    {
        $user2 = TestUser::create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ]);

        $this->ratingService->rate($this->user, $this->post, RatingLevel::FIVE);
        $this->ratingService->rate($user2, $this->post, RatingLevel::FIVE);

        $count = $this->ratingService->countRatingsByLevel($this->post, RatingLevel::FIVE);

        $this->assertSame(2, $count);
    }
}
