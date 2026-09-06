<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('amo_crm_tokens', function (Blueprint $table) {
            $table->id();
            $table->text('access_token')->comment('Зашифрованный access token');
            $table->text('refresh_token')->comment('Зашифрованный refresh token');
            $table->unsignedBigInteger('expires_at')->comment('Время истечения токена (Unix timestamp)');
            $table->string('subdomain', 100)->comment('Субдомен AmoCRM');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('amo_crm_tokens');
    }
};
