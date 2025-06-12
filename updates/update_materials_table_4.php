<?php namespace Pensoft\Courses\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

/**
 * UpdateMaterialsTable4 Migration
 * - Add new target_audiences field (JSON) for multiple selections
 * - Keep existing target_audience field for backward compatibility
 */
class UpdateMaterialsTable4 extends Migration
{
    public function up()
    {
        if (Schema::hasTable('pensoft_courses_materials')) {
            Schema::table('pensoft_courses_materials', function(Blueprint $table) {
                // Add new field for multiple target audiences
                $table->json('target_audiences')->nullable();
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('pensoft_courses_materials')) {
            Schema::table('pensoft_courses_materials', function(Blueprint $table) {
                $table->dropColumn('target_audiences');
            });
        }
    }
} 