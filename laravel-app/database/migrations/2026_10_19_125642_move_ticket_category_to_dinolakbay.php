<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const TICKET_CAT = 3; // Ticket na nakatalaga sa Snackbar

    public function up(): void
    {
        $affected = DB::table('categories')
            ->where('category_id', self::TICKET_CAT)
            ->where('zone', 'Snackbar')
            ->update(['zone' => 'Dinolakbay']);

        if ($affected !== 1) {
            throw new RuntimeException('Hindi nakita ang Ticket category (id 3) sa zone Snackbar.');
        }
    }

    public function down(): void
    {
        DB::table('categories')
            ->where('category_id', self::TICKET_CAT)
            ->where('zone', 'Dinolakbay')
            ->update(['zone' => 'Snackbar']);
    }
};