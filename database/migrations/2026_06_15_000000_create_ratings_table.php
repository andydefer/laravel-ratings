<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->morphs('rater');
            $table->morphs('rateable');
            $table->unsignedTinyInteger('rating_level');
            $table->text('review')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['rater_type', 'rater_id', 'rateable_type', 'rateable_id'], 'ratings_unique');
            $table->index('rating_level');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ratings');
    }
};
