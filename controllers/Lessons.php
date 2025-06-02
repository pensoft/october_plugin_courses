<?php namespace Pensoft\Courses\Controllers;

use BackendMenu;
use Backend\Classes\Controller;

/**
 * Lessons Back-end Controller
 */
class Lessons extends Controller
{
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController',
        'Backend.Behaviors.RelationController',
        'Backend.Behaviors.ReorderController'
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';
    public $relationConfig = 'config_relation.yaml';
    public $reorderConfig = 'config_reorder.yaml';

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Pensoft.Courses', 'courses', 'lessons');
    }
    
    /**
     * Handle the automatic assignment of the lesson_id when creating a material
     */
    public function relationExtendManageWidget($widget, $field, $model)
    {
        // If we're managing materials, auto-assign the lesson ID
        if ($field === 'materials') {
            $widget->bindEvent('form.filling', function($fields, $data) use ($model) {
                // Set the default lesson value to be the parent lesson
                $fields->lesson->value = $model->id;
            });
            
            $widget->bindEvent('form.create', function($fields, $model) {
                // When model is being created, ensure lesson is set correctly
                $model->lesson_id = post('Lesson');
            });
        }
    }
} 