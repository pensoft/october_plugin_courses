<?php namespace Pensoft\Courses\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

/**
 * CreateBlocksTable Migration
 */
class CreateBlocksTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('pensoft_courses_blocks')) {
            Schema::create('pensoft_courses_blocks', function(Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->integer('topic_id')->unsigned();
                $table->string('name')->nullable();
                $table->string('slug')->unique();
                $table->integer('sort_order');
                $table->timestamps();
                
                // Only add foreign key if topics table exists
                if (Schema::hasTable('pensoft_courses_topics')) {
                    $table->foreign('topic_id')->references('id')->on('pensoft_courses_topics')->onDelete('cascade');
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('pensoft_courses_blocks')) {
            Schema::dropIfExists('pensoft_courses_blocks');
        }
    }
}
