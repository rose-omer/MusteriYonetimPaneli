<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_audits', function (Blueprint $table) {
            $table->id();
            // Müşteri kalıcı silinince denetim kaydı kalır; bağ boşalır.
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 20);
            $table->json('changed_fields')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['customer_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_audits');
    }
};
