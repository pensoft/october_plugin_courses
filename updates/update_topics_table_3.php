<?php namespace Pensoft\Courses\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

/**
 * UpdateTopicsTable3 Migration - Add introduction column
 */
class UpdateTopicsTable3 extends Migration
{
    public function up()
    {
        if (Schema::hasTable('pensoft_courses_topics')) {
            Schema::table('pensoft_courses_topics', function(Blueprint $table) {
                $table->text('topic_introduction')->nullable();
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('pensoft_courses_topics')) {
            Schema::table('pensoft_courses_topics', function(Blueprint $table) {
                $table->dropColumn('topic_introduction');
            });
        }
    }
}
