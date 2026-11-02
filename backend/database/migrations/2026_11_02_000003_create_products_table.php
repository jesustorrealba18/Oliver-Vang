<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * El producto "padre".
     * El PRECIO vive aquí; el STOCK NO (vive en variants).
     * Si se borra el drop, el producto sobrevive sin colección (SET NULL).
     * No se puede borrar una categoría con productos (RESTRICT).
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
            $table->foreignId('collection_id')->nullable()->constrained('collections')->nullOnDelete();
            $table->string('name', 150);
            $table->string('slug', 170)->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2); // NUNCA float para dinero
            $table->enum('status', ['published', 'sold_out', 'coming_soon', 'draft'])->default('draft');
            $table->boolean('featured')->default(false);
            $table->string('main_image')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('featured');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
