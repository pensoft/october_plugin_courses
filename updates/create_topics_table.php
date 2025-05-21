<?php namespace Pensoft\Courses\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

/**
 * CreateTopicsTable Migration
 */
class CreateTopicsTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('pensoft_courses_topics')) {
            Schema::create('pensoft_courses_topics', function(Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->string('name')->nullable();
                $table->string('language')->nullable();
                $table->string('slug')->unique();
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('pensoft_courses_topics')) {
            Schema::dropIfExists('pensoft_courses_topics');
        }
    }
}
