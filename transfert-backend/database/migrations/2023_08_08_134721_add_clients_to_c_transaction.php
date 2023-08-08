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
        Schema::table('transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('Client_Envoeyur_Id');
            $table->unsignedBigInteger('Client_Receveur_Id');
            $table->foreign('Client_Envoeyur_Id')->references('id')->on('clients');
            $table->foreign('Client_Receveur_Id')->references('id')->on('clients');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign('Client_Envoeyur_Id');
            $table->dropForeign('Client_Receveur_Id');
        });
    }
};
