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
        DB::statement('CREATE SEQUENCE cart_items_public_id_seq START 1 MINVALUE 1');
        
        Schema::create('cart_items', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->bigInteger('public_id')->unique()->default(DB::raw("nextval('cart_items_public_id_seq')"));
            $table->uuid('user_id');
            $table->uuid('product_id');
            $table->integer('quantity')->default(1);
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            
            $table->unique(['user_id', 'product_id']);
            $table->index('public_id');
            $table->index('user_id');
            $table->index('product_id');
        });
        
        // Agregar constraint de CHECK para quantity > 0
        DB::statement('ALTER TABLE cart_items ADD CONSTRAINT check_quantity_positive CHECK (quantity > 0)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_items');
        DB::statement('DROP SEQUENCE IF EXISTS cart_items_public_id_seq');
    }
};
