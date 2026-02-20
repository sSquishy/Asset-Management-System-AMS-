<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

class RenameCategoriesParentToParentId extends Migration
{
    public function up()
    {
        // Add new nullable parent_id column, copy values from old `parent` if present, then drop old column
        if (! Schema::hasColumn('categories', 'parent_id')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->integer('parent_id')->nullable()->after('name');
            });
        }

        // Only attempt to copy/drop if the legacy `parent` column exists
        if (Schema::hasColumn('categories', 'parent')) {
            // Copy existing values from parent -> parent_id, only when parent is not null
            DB::table('categories')->whereNotNull('parent')->update(['parent_id' => DB::raw('parent')]);

            Schema::table('categories', function (Blueprint $table) {
                if (Schema::hasColumn('categories', 'parent')) {
                    $table->dropColumn('parent');
                }
            });
        }
    }

    public function down()
    {
        // Restore legacy `parent` column only if it does not exist
        if (! Schema::hasColumn('categories', 'parent')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->integer('parent')->default(0)->after('name');
            });
        }

        if (Schema::hasColumn('categories', 'parent_id')) {
            DB::table('categories')->whereNotNull('parent_id')->update(['parent' => DB::raw('parent_id')]);

            Schema::table('categories', function (Blueprint $table) {
                if (Schema::hasColumn('categories', 'parent_id')) {
                    $table->dropColumn('parent_id');
                }
            });
        }
    }
}
