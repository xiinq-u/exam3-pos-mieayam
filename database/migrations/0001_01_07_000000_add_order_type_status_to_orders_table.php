<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambah informasi tambahan di pesanan.
     * order_type untuk dine in/take away, status untuk pending/completed.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('order_type')->default('dine_in')->after('payment_method');
            $table->string('status')->default('completed')->after('order_type');
            $table->string('barcode_reference')->nullable()->after('paid_amount');
        });
    }

    /**
     * Menghapus kolom tambahan jika migration dibatalkan.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['order_type', 'status', 'barcode_reference']);
        });
    }
};
