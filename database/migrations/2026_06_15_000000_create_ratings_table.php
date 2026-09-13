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

            // Longueur limitée à 191 pour permettre l'index unique composite en utf8mb4
            $table->string('rater_type', 191);
            $table->string('rater_id', 191);
            $table->string('rateable_type', 191);
            $table->string('rateable_id', 191);

            $table->unsignedTinyInteger('rating_level');
            $table->text('review')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['rater_type', 'rater_id'], 'ratings_rater_index');
            $table->index(['rateable_type', 'rateable_id'], 'ratings_rateable_index');

            $table->unique(
                ['rater_type', 'rater_id', 'rateable_type', 'rateable_id'],
                'ratings_unique',
            );

            $table->index('rating_level');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ratings');
    }
};
