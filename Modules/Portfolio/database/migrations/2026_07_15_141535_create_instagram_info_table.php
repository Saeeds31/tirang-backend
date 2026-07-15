<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('instagram_info', function (Blueprint $table) {
            $table->id();
            $table->foreignId('portfolio_id')->constrained('portfolios')->onDelete('cascade');
            $table->string('like_count');
            $table->string('view_count');
            $table->string('reach_count');
            $table->string('follower_count');
            $table->string('mounth_count');
            $table->string('brand_logo');
            $table->string('insta_base_image');
            $table->string('first_image');
            $table->string('second_image');
            $table->string('third_image');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('instagram_info');
    }
};
