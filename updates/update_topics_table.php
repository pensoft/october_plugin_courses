<?php namespace Pensoft\Courses\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

/**
 * UpdateMaterialsTable Migration
 */
class UpdateTopicsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('pensoft_courses_topics')) {
            Schema::table('pensoft_courses_topics', function(Blueprint $table) {
                $table->text('instruction')->nullable();
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('pensoft_courses_topics')) {
            Schema::table('pensoft_courses_topics', function(Blueprint $table) {
                $table->dropColumn(['instruction']);
            });
        }
    }
} 