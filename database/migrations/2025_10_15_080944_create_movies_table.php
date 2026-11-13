<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movies', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('duration')->comment('thời lượng (phút)');
            $table->string('poster')->nullable(); // ảnh bìa phim
            $table->string('trailer_url')->nullable(); // link YouTube
            $table->date('release_date')->nullable();
            $table->enum('status', ['Đang chiếu', 'Sắp chiếu'])->default('Sắp chiếu');
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('movies');
    }
};
