<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // TODO: put the EXACT category names of your snackbar categories here
    private array $snackbarCategories = [
        'Snacks',
        'Beverages',
        // 'Add more...'
    ];

    public function up(): void
    {
        // Only needed if categories.zone is an ENUM. Safe to remove if it is VARCHAR.
        $col = DB::selectOne("SHOW COLUMNS FROM categories WHERE Field = 'zone'");
        if ($col && str_starts_with(strtolower($col->Type), 'enum')) {
            DB::statement("ALTER TABLE categories MODIFY zone ENUM('Skataya','Snackbar','Piyestarides','Dinolakbay') NOT NULL");
        }

        $placeholders = implode(',', array_fill(0, count($this->snackbarCategories), '?'));
        DB::update(
            "UPDATE categories SET zone = 'Snackbar' WHERE zone = 'Skataya' AND category_name IN ($placeholders)",
            $this->snackbarCategories
        );
    }

    public function down(): void
    {
        $placeholders = implode(',', array_fill(0, count($this->snackbarCategories), '?'));
        DB::update(
            "UPDATE categories SET zone = 'Skataya' WHERE zone = 'Snackbar' AND category_name IN ($placeholders)",
            $this->snackbarCategories
        );
    }
};