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
        DB::statement('CREATE SEQUENCE products_public_id_seq START 1 MINVALUE 1');
        
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->bigInteger('public_id')->unique()->default(DB::raw("nextval('products_public_id_seq')"));
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->integer('stock_quantity')->default(0);
            $table->string('image')->nullable();
            $table->integer('low_stock_threshold')->default(10);
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();
            
            $table->index('public_id');
            $table->index('is_active');
            $table->index('stock_quantity');
            $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
        DB::statement('DROP SEQUENCE IF EXISTS products_public_id_seq');
    }
};
