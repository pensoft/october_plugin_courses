<?php namespace Pensoft\Courses\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

/**
 * UpdateBlocksTable2 Migration
 * Adds block_number field to blocks table
 */
class UpdateBlocksTable2 extends Migration
{
    public function up()
    {
        if (Schema::hasTable('pensoft_courses_blocks') && !Schema::hasColumn('pensoft_courses_blocks', 'block_number')) {
            Schema::table('pensoft_courses_blocks', function(Blueprint $table) {
                $table->string('block_number')->nullable()->after('name');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('pensoft_courses_blocks') && Schema::hasColumn('pensoft_courses_blocks', 'block_number')) {
            Schema::table('pensoft_courses_blocks', function(Blueprint $table) {
                $table->dropColumn('block_number');
            });
        }
    }
}

