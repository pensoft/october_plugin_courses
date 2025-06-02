<?php namespace Pensoft\Courses\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

/**
 * UpdateTopicsTable2 Migration - Rename instruction to institution
 */
class UpdateTopicsTable2 extends Migration
{
    public function up()
    {
        if (Schema::hasTable('pensoft_courses_topics')) {
            Schema::table('pensoft_courses_topics', function(Blueprint $table) {
                $table->renameColumn('instruction', 'institution');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('pensoft_courses_topics')) {
            Schema::table('pensoft_courses_topics', function(Blueprint $table) {
                $table->renameColumn('institution', 'instruction');
            });
        }
    }
}
