<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->foreignId('category_id')
                ->constrained()
                ->restrictOnDelete();

            $table->string('sku', 64)->unique();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2);
            $table->integer('stock_quantity')->default(0);
            $table->timestamps();

            $table->index('category_id');
            $table->index('name');
        });

        DB::statement(
            'ALTER TABLE products ADD CONSTRAINT products_price_nonnegative CHECK (price >= 0)'
        );

        DB::statement(
            'ALTER TABLE products ADD CONSTRAINT products_stock_nonnegative CHECK (stock_quantity >= 0)'
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
