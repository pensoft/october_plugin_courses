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
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->string('group')->nullable();
                $table->text('description')->nullable();
                $table->timestamps();
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