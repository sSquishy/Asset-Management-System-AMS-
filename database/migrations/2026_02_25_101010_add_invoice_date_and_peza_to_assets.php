<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInvoiceDateAndPezaToAssets extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('assets', function (Blueprint $table) {
            if (!Schema::hasColumn('assets', 'invoice_date')) {
                $table->date('invoice_date')->nullable()->default(null)->after('order_number');
            }
            if (!Schema::hasColumn('assets', 'peza_purchased')) {
                $table->boolean('peza_purchased')->nullable()->default(false)->after('invoice_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('assets', function (Blueprint $table) {
            if (Schema::hasColumn('assets', 'invoice_date')) {
                $table->dropColumn('invoice_date');
            }
            if (Schema::hasColumn('assets', 'peza_purchased')) {
                $table->dropColumn('peza_purchased');
            }
        });
    }
}
