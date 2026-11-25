<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Klantfeedback 25-11: Financial services krijgt het blok "Proof that counts".
     * Het blok verschijnt zodra de klant er logo's aan toevoegt.
     */
    public function up(): void
    {
        DB::table('industries')
            ->where('slug', 'financial-services')
            ->where(fn ($query) => $query->whereNull('proof_title')->orWhere('proof_title', ''))
            ->update(['proof_title' => 'Proof that counts', 'updated_at' => now()]);
    }

    public function down(): void
    {
        DB::table('industries')
            ->where('slug', 'financial-services')
            ->where('proof_title', 'Proof that counts')
            ->update(['proof_title' => null]);
    }
};
