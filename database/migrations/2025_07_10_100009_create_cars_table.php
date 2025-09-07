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
        Schema::create('cars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // مالك السيارة
            $table->string('make'); // اسم الشركة المصنعة
            $table->string('model'); // الموديل
            $table->integer('year'); // سنة الصنع
            $table->string('condition'); // 'new' or 'used' (جديدة أو مستعملة)
            $table->text('description')->nullable();
            $table->string('location'); // موقع السيارة (مدينة)
            $table->json('images')->nullable(); // مسارات الصور
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cars');
    }
};
