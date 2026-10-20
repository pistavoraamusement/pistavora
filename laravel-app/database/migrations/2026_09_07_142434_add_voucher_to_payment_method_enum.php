<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE sales_transactions 
            MODIFY COLUMN payment_method VARCHAR(50) NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE sales_transactions 
            MODIFY COLUMN payment_method 
            ENUM('Cash','Card','GCash','Maya','StarDeals','Klook','Online') 
            NOT NULL");
    }
};