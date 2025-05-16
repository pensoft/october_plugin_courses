<?php namespace Pensoft\Courses\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

/**
 * CreateLessonsTable Migration
 */
class CreateLessonsTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('pensoft_courses_lessons')) {
            Schema::create('pensoft_courses_lessons', function(Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->integer('block_id')->unsigned();
                $table->string('name')->nullable();
                $table->string('slug')->unique();
                $table->integer('sort_order');
                $table->timestamps();
                
                // Only add foreign key if blocks table exists
                if (Schema::hasTable('pensoft_courses_blocks')) {
                    $table->foreign('block_id')->references('id')->on('pensoft_courses_blocks')->onDelete('cascade');
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('pensoft_courses_lessons')) {
            Schema::dropIfExists('pensoft_courses_lessons');
        }
    }
}
