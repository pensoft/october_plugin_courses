<?php namespace Pensoft\Courses\Controllers;

use BackendMenu;
use Backend\Classes\Controller;

/**
 * Topic Backend Controller
 */
class Topic extends Controller
{
    public $implement = [
        \Backend\Behaviors\FormController::class,
        \Backend\Behaviors\ListController::class,
        \Backend\Behaviors\RelationController::class
    ];

    /**
     * @var string formConfig file
     */
    public $formConfig = 'config_form.yaml';

    /**
     * @var string listConfig file
     */
    public $listConfig = 'config_list.yaml';
    
    /**
     * @var string relationConfig file
     */
    public $relationConfig = 'config_relation.yaml';

    /**
     * __construct the controller
     */
    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Pensoft.Courses', 'courses', 'topics');
    }
}
