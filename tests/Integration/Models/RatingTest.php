<?php

declare(strict_types=1);

namespace AndyDefer\LaravelRatings\Tests\Integration\Models;

use AndyDefer\DomainStructures\Utils\StrictDataObject;
use AndyDefer\LaravelRatings\Database\Factories\RatingFactory;
use AndyDefer\LaravelRatings\Enums\RatingLevel;
use AndyDefer\LaravelRatings\Models\Rating;
use AndyDefer\LaravelRatings\Tests\Fixtures\Models\TestPost;
use AndyDefer\LaravelRatings\Tests\Fixtures\Models\TestUser;
use AndyDefer\LaravelRatings\Tests\IntegrationTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

final class RatingTest extends IntegrationTestCase
{
    use RefreshDatabase;

    private TestUser $user;

    private TestPost $post;

    protected function setUp(): void
    {
        parent::setUp();

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

    // ============================================================
    // ATTRIBUTE ACCESSOR TESTS
    // ============================================================

    public function test_metadata_returns_strict_data_object(): void
    {
        $metadata = ['source' => 'web', 'order_id' => 12345];

        $rating = RatingFactory::new()
            ->rater($this->user)
            ->rateable($this->post)
            ->fiveStars()
            ->withMetadata($metadata)
            ->create();

        $this->assertInstanceOf(StrictDataObject::class, $rating->metadata);
        $this->assertSame('web', $rating->metadata->get('source'));
        $this->assertSame(12345, $rating->metadata->get('order_id'));
    }

    public function test_metadata_returns_null_when_no_metadata(): void
    {
        $rating = RatingFactory::new()
            ->rater($this->user)
            ->rateable($this->post)
            ->fiveStars()
            ->withoutMetadata()
            ->create();

        $this->assertNull($rating->metadata);
    }

    public function test_rating_level_returns_rating_level_enum(): void
    {
        $rating = RatingFactory::new()
            ->rater($this->user)
            ->rateable($this->post)
            ->fiveStars()
            ->create();

        $this->assertInstanceOf(RatingLevel::class, $rating->rating_level);
        $this->assertSame(RatingLevel::FIVE, $rating->rating_level);
    }

    public function test_rating_level_with_different_levels(): void
    {
        $levels = [
            RatingLevel::ONE,
            RatingLevel::TWO,
            RatingLevel::THREE,
            RatingLevel::FOUR,
            RatingLevel::FIVE,
        ];

        foreach ($levels as $index => $level) {
            $user = TestUser::create([
                'name' => "User {$index}",
                'email' => "user{$index}@example.com",
            ]);

            $post = TestPost::create([
                'user_id' => $user->id,
                'title' => "Post {$index}",
                'body' => "Content {$index}",
            ]);

            $rating = RatingFactory::new()
                ->rater($user)
                ->rateable($post)
                ->level($level)
                ->create();

            $this->assertSame($level, $rating->rating_level);
        }
    }

    public function test_review_returns_string(): void
    {
        $review = 'Excellent produit ! Je recommande vivement.';

        $rating = RatingFactory::new()
            ->rater($this->user)
            ->rateable($this->post)
            ->fiveStars()
            ->withReview($review)
            ->create();

        $this->assertSame($review, $rating->review);
    }

    public function test_review_returns_null_when_no_review(): void
    {
        $rating = RatingFactory::new()
            ->rater($this->user)
            ->rateable($this->post)
            ->fiveStars()
            ->withoutReview()
            ->create();

        $this->assertNull($rating->review);
    }

    // ============================================================
    // RELATION TESTS
    // ============================================================

    public function test_rater_returns_the_correct_model(): void
    {
        $rating = RatingFactory::new()
            ->rater($this->user)
            ->rateable($this->post)
            ->fiveStars()
            ->create();

        $rater = $rating->rater;

        $this->assertInstanceOf(TestUser::class, $rater);
        $this->assertSame($this->user->id, $rater->id);
    }

    public function test_rateable_returns_the_correct_model(): void
    {
        $rating = RatingFactory::new()
            ->rater($this->user)
            ->rateable($this->post)
            ->fiveStars()
            ->create();

        $rateable = $rating->rateable;

        $this->assertInstanceOf(TestPost::class, $rateable);
        $this->assertSame($this->post->id, $rateable->id);
    }

    public function test_rater_returns_null_when_model_deleted(): void
    {
        $rating = RatingFactory::new()
            ->rater($this->user)
            ->rateable($this->post)
            ->fiveStars()
            ->create();

        $this->user->delete();

        $fresh = Rating::find($rating->id);

        $this->assertNull($fresh->rater);
    }

    public function test_rateable_returns_null_when_model_deleted(): void
    {
        $rating = RatingFactory::new()
            ->rater($this->user)
            ->rateable($this->post)
            ->fiveStars()
            ->create();

        $this->post->delete();

        $fresh = Rating::find($rating->id);

        $this->assertNull($fresh->rateable);
    }

    // ============================================================
    // SOFT DELETE TESTS
    // ============================================================

    public function test_soft_delete_excludes_from_default_queries(): void
    {
        $rating = RatingFactory::new()
            ->rater($this->user)
            ->rateable($this->post)
            ->fiveStars()
            ->create();

        $rating->delete();

        $found = Rating::find($rating->id);

        $this->assertNull($found);
    }

    public function test_with_trashed_includes_deleted_ratings(): void
    {
        $rating = RatingFactory::new()
            ->rater($this->user)
            ->rateable($this->post)
            ->fiveStars()
            ->create();

        $rating->delete();

        $found = Rating::withTrashed()->find($rating->id);

        $this->assertNotNull($found);
        $this->assertNotNull($found->deleted_at);
    }

    public function test_restore_recovers_soft_deleted_rating(): void
    {
        $rating = RatingFactory::new()
            ->rater($this->user)
            ->rateable($this->post)
            ->fiveStars()
            ->create();

        $rating->delete();
        $rating->restore();

        $found = Rating::find($rating->id);

        $this->assertNotNull($found);
        $this->assertNull($found->deleted_at);
    }

    public function test_force_delete_permanently_removes_rating(): void
    {
        $rating = RatingFactory::new()
            ->rater($this->user)
            ->rateable($this->post)
            ->fiveStars()
            ->create();

        $rating->forceDelete();

        $found = Rating::withTrashed()->find($rating->id);

        $this->assertNull($found);
    }

    // ============================================================
    // METADATA TESTS
    // ============================================================

    public function test_metadata_with_complex_data(): void
    {
        $metadata = [
            'source' => 'mobile',
            'device' => 'iPhone 15',
            'os' => 'iOS 17.2',
            'app_version' => '2.1.0',
            'session_id' => 'abc-123-def-456',
            'timestamp' => '2024-01-15 10:30:00',
        ];

        $rating = RatingFactory::new()
            ->rater($this->user)
            ->rateable($this->post)
            ->fiveStars()
            ->withMetadata($metadata)
            ->create();

        $this->assertInstanceOf(StrictDataObject::class, $rating->metadata);
        $this->assertSame('mobile', $rating->metadata->get('source'));
        $this->assertSame('iPhone 15', $rating->metadata->get('device'));
        $this->assertSame('iOS 17.2', $rating->metadata->get('os'));
        $this->assertSame('2.1.0', $rating->metadata->get('app_version'));
        $this->assertSame('abc-123-def-456', $rating->metadata->get('session_id'));
        $this->assertSame('2024-01-15 10:30:00', $rating->metadata->get('timestamp'));
    }

    public function test_metadata_persists_json_correctly(): void
    {
        $metadata = ['source' => 'api', 'user_id' => 42];

        $rating = RatingFactory::new()
            ->rater($this->user)
            ->rateable($this->post)
            ->fiveStars()
            ->withMetadata($metadata)
            ->create();

        $this->assertDatabaseHas('ratings', [
            'id' => $rating->id,
            'metadata' => json_encode($metadata),
        ]);
    }

    // ============================================================
    // FACTORY TESTS
    // ============================================================

    public function test_factory_creates_rating_with_defaults(): void
    {
        $rating = RatingFactory::new()
            ->rater($this->user)
            ->rateable($this->post)
            ->create();

        $this->assertNotNull($rating->id);
        $this->assertNotNull($rating->rating_level);
        $this->assertSame($this->user->id, $rating->rater_id);
        $this->assertSame($this->post->id, $rating->rateable_id);
    }

    public function test_factory_five_stars_creates_rating_with_five_stars(): void
    {
        $rating = RatingFactory::new()
            ->rater($this->user)
            ->rateable($this->post)
            ->fiveStars()
            ->create();

        $this->assertSame(RatingLevel::FIVE, $rating->rating_level);
    }

    public function test_factory_complete_creates_rating_with_all_fields(): void
    {
        $user2 = TestUser::create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ]);

        $post2 = TestPost::create([
            'user_id' => $user2->id,
            'title' => 'Second Post',
            'body' => 'Another content',
        ]);

        $metadata = ['source' => 'web', 'order_id' => 999];

        $rating = RatingFactory::new()
            ->complete(
                rater: $this->user,
                rateable: $post2,
                level: RatingLevel::FOUR,
                review: 'Good post',
                metadata: $metadata
            )
            ->create();

        $this->assertSame($this->user->id, $rating->rater_id);
        $this->assertSame(TestUser::class, $rating->rater_type);
        $this->assertSame($post2->id, $rating->rateable_id);
        $this->assertSame(TestPost::class, $rating->rateable_type);
        $this->assertSame(RatingLevel::FOUR, $rating->rating_level);
        $this->assertSame('Good post', $rating->review);
        $this->assertSame('web', $rating->metadata->get('source'));
        $this->assertSame(999, $rating->metadata->get('order_id'));
    }

    public function test_factory_from_source_sets_source_in_metadata(): void
    {
        $rating = RatingFactory::new()
            ->rater($this->user)
            ->rateable($this->post)
            ->fiveStars()
            ->fromSource('mobile')
            ->create();

        $this->assertSame('mobile', $rating->metadata->get('source'));
    }

    public function test_factory_with_random_metadata_creates_metadata(): void
    {
        $rating = RatingFactory::new()
            ->rater($this->user)
            ->rateable($this->post)
            ->fiveStars()
            ->withRandomMetadata()
            ->create();

        $this->assertNotNull($rating->metadata);
        $this->assertNotNull($rating->metadata->get('ip'));
        $this->assertNotNull($rating->metadata->get('user_agent'));
        $this->assertNotNull($rating->metadata->get('source'));
    }

    // ============================================================
    // EDGE CASES
    // ============================================================

    public function test_rating_with_empty_metadata_stores_empty_array(): void
    {
        $rating = RatingFactory::new()
            ->rater($this->user)
            ->rateable($this->post)
            ->fiveStars()
            ->withMetadata([])
            ->create();

        $this->assertNotNull($rating->metadata);
        $this->assertTrue($rating->metadata->isEmpty());
    }

    public function test_multiple_ratings_on_same_rateable(): void
    {
        $user2 = TestUser::create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ]);

        $rating1 = RatingFactory::new()
            ->rater($this->user)
            ->rateable($this->post)
            ->fiveStars()
            ->create();

        $rating2 = RatingFactory::new()
            ->rater($user2)
            ->rateable($this->post)
            ->fourStars()
            ->create();

        $ratings = Rating::where('rateable_id', $this->post->id)
            ->where('rateable_type', TestPost::class)
            ->get();

        $this->assertCount(2, $ratings);
        $this->assertSame(RatingLevel::FIVE, $rating1->rating_level);
        $this->assertSame(RatingLevel::FOUR, $rating2->rating_level);
    }

    public function test_same_user_can_rate_different_rateables(): void
    {
        $post2 = TestPost::create([
            'user_id' => $this->user->id,
            'title' => 'Second Post',
            'body' => 'Another content',
        ]);

        $rating1 = RatingFactory::new()
            ->rater($this->user)
            ->rateable($this->post)
            ->fiveStars()
            ->create();

        $rating2 = RatingFactory::new()
            ->rater($this->user)
            ->rateable($post2)
            ->fourStars()
            ->create();

        $userRatings = Rating::where('rater_id', $this->user->id)
            ->where('rater_type', TestUser::class)
            ->get();

        $this->assertCount(2, $userRatings);
        $this->assertSame($this->post->id, $rating1->rateable_id);
        $this->assertSame($post2->id, $rating2->rateable_id);
    }
}
