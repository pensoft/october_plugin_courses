<?php namespace Pensoft\Courses\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

/**
 * CreateMaterialsTable Migration
 */
class CreateMaterialsTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('pensoft_courses_materials')) {
            Schema::create('pensoft_courses_materials', function(Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->integer('lesson_id')->unsigned();
                $table->string('name')->nullable();
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->string('type')->nullable();
                $table->integer('sort_order');
                $table->timestamps();
                
                // Only add foreign key if lessons table exists
                if (Schema::hasTable('pensoft_courses_lessons')) {
                    $table->foreign('lesson_id')->references('id')->on('pensoft_courses_lessons')->onDelete('cascade');
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('pensoft_courses_materials')) {
            Schema::dropIfExists('pensoft_courses_materials');
        }
    }
}
