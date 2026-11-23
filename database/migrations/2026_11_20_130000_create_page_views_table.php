<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Weergaven van cases, blogs en jobs, opgeteld per dag (geen IP's of cookies van bezoekers).
     */
    public function up(): void
    {
        Schema::create('page_views', function (Blueprint $table) {
            $table->id();
            $table->string('viewable_type', 20);
            $table->unsignedBigInteger('viewable_id');
            $table->date('viewed_on');
            $table->unsignedInteger('views')->default(0);

            $table->unique(['viewable_type', 'viewable_id', 'viewed_on']);
            $table->index(['viewable_type', 'viewed_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_views');
    }
};
