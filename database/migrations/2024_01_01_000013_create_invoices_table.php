<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->onDelete('cascade');
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->string('invoice_number');
            $table->string('invoice_title')->default('INVOICE');
            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable();
            $table->string('date');
            $table->string('due_date');
            $table->string('po_number')->nullable();
            $table->decimal('global_discount', 15, 2)->default(0);
            $table->decimal('global_tax', 15, 2)->default(0);
            $table->decimal('shipping', 15, 2)->default(0);
            $table->string('payment_status')->default('unpaid');
            $table->string('status')->default('unpaid');
            $table->json('payments')->nullable();
            $table->string('payment_method')->nullable();
            $table->text('terms')->nullable();
            $table->text('signature')->nullable();
            $table->string('currency')->default('IDR Rp');
            $table->string('template')->default('simple');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('invoices');
    }
};
