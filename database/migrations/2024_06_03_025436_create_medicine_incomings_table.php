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
        Schema::create('medicine_incoming', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_medicine')->constrained(table: 'medicines');
            $table->foreignId('id_unit')->constrained(table: 'units');
            $table->string('batch_no');
            $table->date('exp_date');
            $table->integer('quantity');
            $table->date('date');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicine_incoming');
    }
};
