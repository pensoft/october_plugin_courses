<?php namespace Pensoft\Courses\Controllers;

use BackendMenu;
use Backend\Classes\Controller;
use Input;
use Flash;
use Pensoft\Courses\Models\Topic;

/**
 * Blocks Back-end Controller
 */
class Blocks extends Controller
{
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController',
        'Backend.Behaviors.ReorderController'
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';
    public $reorderConfig = 'config_reorder.yaml';

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Pensoft.Courses', 'courses', 'blocks');
    }
    
    public function create()
    {
        $this->bodyClass = 'compact-container';
        $this->asExtension('FormController')->create();
        
        if ($topicId = Input::get('topic_id')) {
            if ($topic = Topic::find($topicId)) {
                $this->vars['topicName'] = $topic->name;
                $this->vars['defaultTopicId'] = $topicId;
            }
        }
    }
} 