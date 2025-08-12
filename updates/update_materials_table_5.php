<?php namespace Pensoft\Courses\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

/**
 * UpdateMaterialsTable5 Migration
 * - Add slideshare_url field for embedding SlideShare presentations
 */
class UpdateMaterialsTable5 extends Migration
{
    public function up()
    {
        if (Schema::hasTable('pensoft_courses_materials')) {
            Schema::table('pensoft_courses_materials', function (Blueprint $table) {
                $table->text('slideshare_url')->nullable();
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('pensoft_courses_materials')) {
            Schema::table('pensoft_courses_materials', function (Blueprint $table) {
                $table->dropColumn('slideshare_url');
            });
        }
    }
}


