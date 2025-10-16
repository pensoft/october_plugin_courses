<?php namespace Pensoft\Courses\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

/**
 * UpdateBlocksTable Migration
 * Adds language field to blocks table
 */
class UpdateBlocksTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('pensoft_courses_blocks') && !Schema::hasColumn('pensoft_courses_blocks', 'language')) {
            Schema::table('pensoft_courses_blocks', function(Blueprint $table) {
                $table->string('language')->nullable()->after('level');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('pensoft_courses_blocks') && Schema::hasColumn('pensoft_courses_blocks', 'language')) {
            Schema::table('pensoft_courses_blocks', function(Blueprint $table) {
                $table->dropColumn('language');
            });
        }
    }
}
