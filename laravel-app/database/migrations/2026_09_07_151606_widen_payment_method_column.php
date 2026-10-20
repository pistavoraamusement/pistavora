<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // VARCHAR imbes na ENUM — mas flexible, hindi na kailangan mag-migrate
        // ulit tuwing may bagong payment method (Online, Voucher, atbp.)
        DB::statement("ALTER TABLE sales_transactions 
            MODIFY COLUMN payment_method VARCHAR(50) NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE sales_transactions 
            MODIFY COLUMN payment_method 
            ENUM('Cash','Card','GCash','Maya','StarDeals','Klook') 
            NOT NULL");
    }
};