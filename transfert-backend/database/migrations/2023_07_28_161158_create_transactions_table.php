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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('envoyeur_id');
            $table->unsignedBigInteger('receveur_id')->nullable();
            $table->string("type_transaction");
            $table->date('date_transaction');
            $table->float('montant');
            $table->float('frais');
            $table->string('code_transaction');
            $table->boolean('permanent')->default(null);
            $table->foreign('envoyeur_id')->references('id')->on('comptes')->onDelete('cascade');
            $table->foreign('receveur_id')->references('id')->on('comptes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
