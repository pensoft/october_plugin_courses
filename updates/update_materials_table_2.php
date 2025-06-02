<?php namespace Pensoft\Courses\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

/**
 * UpdateMaterialsTable Migration
 */
class UpdateMaterialsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('pensoft_courses_materials')) {
            Schema::table('pensoft_courses_materials', function(Blueprint $table) {
                $table->text('instruction')->nullable();
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('pensoft_courses_materials')) {
            Schema::table('pensoft_courses_materials', function(Blueprint $table) {
                $table->dropColumn(['instruction']);
            });
        }
    }
} 