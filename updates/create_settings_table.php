<?php namespace Pensoft\Courses\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

/**
 * CreateSettingsTable Migration
 */
class CreateSettingsTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('pensoft_courses_settings')) {
            Schema::create('pensoft_courses_settings', function(Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->enum('type', ['block_level', 'material_type']);
                $table->string('value')->index();
                $table->string('label');
                $table->integer('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                
                // Unique constraint for value within the same type
                $table->unique(['type', 'value']);
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('pensoft_courses_settings')) {
            Schema::dropIfExists('pensoft_courses_settings');
        }
    }
} 