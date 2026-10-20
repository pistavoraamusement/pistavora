<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $map = [
        'Dino Adventure' => 'Dinolakbay',
        'Roller Fever'   => 'Skataya',
        'Field of Rides' => 'Piyestarides',
    ];

    public function up(): void
    {
        foreach ($this->map as $old => $new) {
            DB::table('categories')
                ->whereRaw('LOWER(zone) = ?', [strtolower($old)])
                ->update(['zone' => $new]);
        }
    }

    public function down(): void
    {
        foreach ($this->map as $old => $new) {
            DB::table('categories')
                ->whereRaw('LOWER(zone) = ?', [strtolower($new)])
                ->update(['zone' => $old]);
        }
    }
};