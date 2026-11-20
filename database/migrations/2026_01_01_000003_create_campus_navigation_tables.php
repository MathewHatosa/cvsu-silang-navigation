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
        if (!Schema::hasTable('buildings')) {
            Schema::create('buildings', function (Blueprint $table) {
                $table->id('building_id');
                $table->string('building_name', 255);
                $table->decimal('latitude', 10, 8);
                $table->decimal('longitude', 11, 8);
                $table->text('description')->nullable();
            });
        }

        if (!Schema::hasTable('rooms')) {
            Schema::create('rooms', function (Blueprint $table) {
                $table->id('room_id');
                $table->unsignedBigInteger('building_id');
                $table->string('room_number', 50);
                $table->string('room_name', 255);

                $table->foreign('building_id')
                    ->references('building_id')
                    ->on('buildings')
                    ->onDelete('cascade');
            });
        }

        if (!Schema::hasTable('departments')) {
            Schema::create('departments', function (Blueprint $table) {
                $table->id('department_id');
                $table->unsignedBigInteger('building_id');
                $table->string('department_name', 255);

                $table->foreign('building_id')
                    ->references('building_id')
                    ->on('buildings')
                    ->onDelete('cascade');
            });
        }

        if (!Schema::hasTable('facilities')) {
            Schema::create('facilities', function (Blueprint $table) {
                $table->id('facility_id');
                $table->string('facility_name', 255);
                $table->decimal('latitude', 10, 8);
                $table->decimal('longitude', 11, 8);
            });
        }

        if (!Schema::hasTable('navigation_history')) {
            Schema::create('navigation_history', function (Blueprint $table) {
                $table->id('history_id');
                $table->unsignedBigInteger('user_id')->nullable();
                $table->unsignedBigInteger('destination_id')->nullable();
                $table->dateTime('date')->useCurrent();

                $table->foreign('destination_id')
                    ->references('building_id')
                    ->on('buildings')
                    ->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('navigation_history');
        Schema::dropIfExists('facilities');
        Schema::dropIfExists('departments');
        Schema::dropIfExists('rooms');
        Schema::dropIfExists('buildings');
    }
};
