<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * La tabla MÁS IMPORTANTE del proyecto.
     * Un hoodie negro en M y el mismo en L son DOS FILAS distintas:
     * el stock pertenece a la variante (color + talla).
     *
     * size es VARCHAR (no enum) porque el calzado usa números
     * y cambiar un ENUM requiere ALTER TABLE.
     */
    public function up(): void
    {
        Schema::create('variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('color', 50);
            $table->char('color_hex', 7)->nullable(); // "#111111" → circulito de color en la UI
            $table->string('size', 20);
            $table->string('sku', 60)->nullable()->unique();
            $table->integer('stock')->default(0);
            $table->timestamps();

            // Impide duplicar "Negro / M" dos veces para el mismo producto
            $table->unique(['product_id', 'color', 'size']);
            $table->index('product_id');
        });

        // CHECK nativo solo en MySQL/InnoDB (referencia: schema.sql).
        // SQLite (dev) lo omite; la app también valida stock >= 0.
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE variants ADD CONSTRAINT chk_stock_nonneg CHECK (stock >= 0)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('variants');
    }
};
