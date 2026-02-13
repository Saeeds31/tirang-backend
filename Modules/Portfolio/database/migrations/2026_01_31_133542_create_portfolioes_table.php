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
        Schema::create('portfolios', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->string("image");
            $table->string("video")->nullable();
            $table->string("meta_title")->nullable();
            $table->string("meta_description")->nullable();
            $table->string("social_link")->nullable();
            $table->string("website_link")->nullable();
            $table->foreignId('employer_id')->constrained('employers')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('portfolio_categories')->onDelete('cascade');
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('portfolios');
    }
};
