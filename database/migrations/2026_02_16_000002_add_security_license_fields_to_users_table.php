<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'middle_name')) {
                $table->string('security_license_number', 191)->nullable()->after('middle_name');
            } else {
                $table->string('security_license_number', 191)->nullable()->after('last_name');
            }
            $table->date('security_license_expiration')->nullable()->after('security_license_number');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['security_license_number', 'security_license_expiration']);
        });
    }
};
