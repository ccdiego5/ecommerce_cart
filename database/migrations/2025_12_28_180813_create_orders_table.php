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
        DB::statement('CREATE SEQUENCE orders_public_id_seq START 1 MINVALUE 1');
        
        Schema::create('orders', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->bigInteger('public_id')->unique()->default(DB::raw("nextval('orders_public_id_seq')"));
            $table->uuid('user_id');
            $table->string('order_number', 50)->unique();
            $table->decimal('total_amount', 10, 2);
            $table->string('status', 20)->default('completed');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            $table->index('public_id');
            $table->index('user_id');
            $table->index('order_number');
            $table->index('status');
            $table->index('completed_at');
        });
        
        // Agregar constraint de CHECK para status
        DB::statement("ALTER TABLE orders ADD CONSTRAINT check_status_valid CHECK (status IN ('pending', 'completed', 'cancelled'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
        DB::statement('DROP SEQUENCE IF EXISTS orders_public_id_seq');
    }
};
