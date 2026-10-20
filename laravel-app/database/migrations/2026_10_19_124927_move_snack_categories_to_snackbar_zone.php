<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // IceCream, Noodles, Sweets
    private array $snackbarCategoryIds = [8, 7, 6];

    public function up(): void
    {
        // Para lang kung ENUM ang categories.zone
        $col = DB::selectOne("SHOW COLUMNS FROM categories WHERE Field = 'zone'");
        if ($col && str_starts_with(strtolower($col->Type), 'enum')) {
            DB::statement("ALTER TABLE categories MODIFY zone ENUM('Skataya','Snackbar','Piyestarides','Dinolakbay') NOT NULL");
        }

        $placeholders = implode(',', array_fill(0, count($this->snackbarCategoryIds), '?'));

        $affected = DB::update(
            "UPDATE categories SET zone = 'Snackbar'
             WHERE zone = 'Skataya' AND category_id IN ($placeholders)",
            $this->snackbarCategoryIds
        );

        if ($affected !== count($this->snackbarCategoryIds)) {
            throw new RuntimeException(
                "Inaasahang {$this->count()} ang ma-update pero {$affected} lang. I-check ang category IDs."
            );
        }
    }

    public function down(): void
    {
        $placeholders = implode(',', array_fill(0, count($this->snackbarCategoryIds), '?'));

        DB::update(
            "UPDATE categories SET zone = 'Skataya'
             WHERE zone = 'Snackbar' AND category_id IN ($placeholders)",
            $this->snackbarCategoryIds
        );
    }

    private function count(): int
    {
        return count($this->snackbarCategoryIds);
    }
};