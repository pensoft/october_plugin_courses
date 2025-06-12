<?php namespace Pensoft\Courses\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

/**
 * UpdateMaterialsTable3 Migration
 */
class UpdateMaterialsTable3 extends Migration
{
    public function up()
    {
        if (Schema::hasTable('pensoft_courses_materials')) {
            Schema::table('pensoft_courses_materials', function(Blueprint $table) {
                $table->text('target_audience')->nullable();
                $table->text('author')->nullable();
                $table->text('contact_information')->nullable();
                $table->text('copyright')->nullable();
                $table->text('link_to_other_materials')->nullable();
                $table->boolean('download_possible')->default(false);
                $table->timestamp('date_of_creation')->nullable();
                $table->timestamp('date_of_version')->nullable();
                $table->timestamp('date_of_upload')->nullable();
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('pensoft_courses_materials')) {
            Schema::table('pensoft_courses_materials', function(Blueprint $table) {
                $table->dropColumn([
                    'target_audience',
                    'author',
                    'contact_information',
                    'copyright',
                    'link_to_other_materials',
                    'download_possible',
                    'date_of_creation',
                    'date_of_version',
                    'date_of_upload'
                ]);
            });
        }
    }
} 