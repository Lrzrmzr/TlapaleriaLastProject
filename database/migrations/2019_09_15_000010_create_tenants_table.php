<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTenantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->string('id')->primary(); // slug único, ej: "ferreteria-garcia"

            $table->string('name');          // Nombre de la empresa
            $table->string('rfc', 13)->nullable(); // RFC México
            $table->string('email')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('logo')->nullable();
            $table->enum('status', ['trial', 'active', 'suspended', 'cancelled'])->default('trial');
            $table->string('plan')->default('basico'); // basico | estandar | premium | enterprise
            $table->timestamp('trial_ends_at')->nullable();
            $table->json('settings')->nullable(); // configuraciones adicionales por tenant

            $table->timestamps();
            $table->json('data')->nullable(); // requerido por stancl/tenancy
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
}
