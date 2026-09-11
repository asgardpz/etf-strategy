<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up(): void { Schema::create('etfs', function(Blueprint $table){$table->id();$table->string('code',12)->unique();$table->string('name');$table->string('market',20)->default('TWSE');$table->boolean('is_active')->default(true);$table->timestamp('last_synced_at')->nullable();$table->timestamps();}); } public function down(): void {Schema::dropIfExists('etfs');} };
