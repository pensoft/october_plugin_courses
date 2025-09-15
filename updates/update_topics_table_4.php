<?php namespace Pensoft\Courses\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

/**
 * UpdateTopicsTable4 Migration - Add description and country columns
 */
class UpdateTopicsTable4 extends Migration
{
    public function up()
    {
        if (Schema::hasTable('pensoft_courses_topics')) {
            Schema::table('pensoft_courses_topics', function(Blueprint $table) {
                $table->text('description')->nullable();
                $table->integer('country_id')->nullable()->unsigned();
                $table->foreign('country_id')->references('id')->on('rainlab_location_countries')->onDelete('set null');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('pensoft_courses_topics')) {
            Schema::table('pensoft_courses_topics', function(Blueprint $table) {
                $table->dropForeign(['country_id']);
                $table->dropColumn(['description', 'country_id']);
            });
        }
    }
}
