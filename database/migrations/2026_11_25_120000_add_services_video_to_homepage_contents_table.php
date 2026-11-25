<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Home V2 toont één doorlopende video voor alle services. Vul hem voorlopig
     * met de video van de eerste service; de klant kiest later een eigen video.
     */
    public function up(): void
    {
        Schema::table('homepage_contents', function (Blueprint $table) {
            $table->string('services_video')->nullable()->after('services');
        });

        foreach (DB::table('homepage_contents')->get(['id', 'services']) as $row) {
            $media = collect(json_decode((string) $row->services, true) ?: [])
                ->pluck('media')
                ->filter()
                ->first();

            if ($media) {
                DB::table('homepage_contents')->where('id', $row->id)->update(['services_video' => (string) $media]);
            }
        }

        $now = now();

        DB::table('floating_pills')->insert([
            'label' => 'Meet the team',
            'href' => '#team',
            'page' => 'home-v2',
            'start_section' => 'about',
            'end_section' => 'about',
            'avatars' => null,
            'use_team_avatars' => true,
            'desktop_bottom' => 11,
            'is_active' => true,
            'sort_order' => 6,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function down(): void
    {
        DB::table('floating_pills')->where('page', 'home-v2')->delete();

        Schema::table('homepage_contents', function (Blueprint $table) {
            $table->dropColumn('services_video');
        });
    }
};
