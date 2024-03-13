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
        Schema::table('orders',function(Blueprint $table){
            $table->enum('payment_status',['Paid','Not Paid'])->after('grand_total')->default('Not Paid');
            $table->enum('status',['Pending','Shipped','Delivered'])->after('payment_status')->default('pending');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders',function(Blueprint $table){
            $table->dropColumn('payment_status');
            $table->dropColumn('status');

        });
    }
};
