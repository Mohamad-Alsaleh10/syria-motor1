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
        Schema::create('service_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // المستخدم الطالب للخدمة
            $table->foreignId('workshop_id')->nullable()->constrained()->onDelete('set null'); // الورشة المختارة (يمكن أن تكون null في البداية)
            $table->string('title');
            $table->text('description');
            $table->string('status')->default('pending'); // 'pending', 'accepted', 'rejected', 'completed', 'cancelled'
            $table->decimal('estimated_cost', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_requests');
    }
};
