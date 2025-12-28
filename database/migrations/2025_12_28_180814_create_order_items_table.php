<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Crear secuencia para public_id
        DB::statement('CREATE SEQUENCE order_items_public_id_seq START 1 MINVALUE 1');
        
        Schema::create('order_items', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->bigInteger('public_id')->unique()->default(DB::raw("nextval('order_items_public_id_seq')"));
            $table->uuid('order_id');
            $table->uuid('product_id');
            $table->string('product_name');
            $table->decimal('product_price', 10, 2);
            $table->integer('quantity');
            $table->decimal('subtotal', 10, 2);
            $table->timestamps();
            
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            
            $table->index('public_id');
            $table->index('order_id');
            $table->index('product_id');
        });
        
        // Agregar constraint de CHECK para quantity > 0
        DB::statement('ALTER TABLE order_items ADD CONSTRAINT check_order_items_quantity_positive CHECK (quantity > 0)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
        DB::statement('DROP SEQUENCE IF EXISTS order_items_public_id_seq');
    }
};
