<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Información de envío
            $table->string('shipping_name')->nullable()->after('status');
            $table->string('shipping_phone')->nullable()->after('shipping_name');
            $table->string('shipping_address')->nullable()->after('shipping_phone');
            $table->string('shipping_city')->nullable()->after('shipping_address');
            $table->string('shipping_state')->nullable()->after('shipping_city');
            $table->string('shipping_zip')->nullable()->after('shipping_state');
            $table->string('shipping_country')->nullable()->after('shipping_zip');
            
            // Información de pago (solo últimos 4 dígitos y tipo)
            $table->string('payment_method')->nullable()->after('shipping_country'); // credit_card, debit_card, etc.
            $table->string('card_last_four')->nullable()->after('payment_method');
            $table->string('card_brand')->nullable()->after('card_last_four'); // visa, mastercard, amex
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'shipping_name',
                'shipping_phone',
                'shipping_address',
                'shipping_city',
                'shipping_state',
                'shipping_zip',
                'shipping_country',
                'payment_method',
                'card_last_four',
                'card_brand',
            ]);
        });
    }
};
