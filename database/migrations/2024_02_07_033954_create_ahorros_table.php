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
        Schema::create('ahorros', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('actividad_id');
            $table->boolean('status')->default(true);
            $table->decimal('valor', 10, 2);
            $table->date('fecha_actividad');
            $table->string('tipo_actividad');
            // Agregar otras columnas necesarias para la tabla ahorros
            $table->timestamps();

            // Definir la clave foránea
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('actividad_id')->references('id')->on('actividades');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ahorros');
    }
};