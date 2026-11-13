<?php

use App\Models\AboutPageContent;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * The "Join our team" link pointed to /careers, which never existed; the vacancies live at /jobs.
     */
    public function up(): void
    {
        AboutPageContent::query()->where('join_team_href', '/careers')->update(['join_team_href' => '/jobs']);
    }

    public function down(): void
    {
        AboutPageContent::query()->where('join_team_href', '/jobs')->update(['join_team_href' => '/careers']);
    }
};
