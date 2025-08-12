<?php namespace Pensoft\Courses\Controllers;

use BackendMenu;
use Backend\Classes\Controller;

/**
 * Materials Back-end Controller (Reorder only)
 */
class Materials extends Controller
{
    public $implement = [
        'Backend.Behaviors.ReorderController',
    ];

    public $reorderConfig = 'config_reorder.yaml';

    public function __construct()
    {
        parent::__construct();

        // Keep Courses -> Lessons menu active context
        BackendMenu::setContext('Pensoft.Courses', 'courses', 'lessons');
    }

    /**
     * Limit reorder list to materials belonging to the provided lesson ID.
     */
    public function reorderExtendQuery($query)
    {
        $lessonId = $this->params[0] ?? post('lesson_id');
        if ($lessonId) {
            $query->where('lesson_id', (int) $lessonId);
        }
    }
}


