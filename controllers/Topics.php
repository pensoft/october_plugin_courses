<?php namespace Pensoft\Courses\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use Backend\Widgets\Form;
use Pensoft\Courses\Models\Block;
use Pensoft\Courses\Models\Lesson;
use Pensoft\Courses\Models\Material;
use Flash;
use Redirect;
use Input;

class Topics extends Controller
{
    public $implement = [
        'Backend\Behaviors\ListController',
        'Backend\Behaviors\FormController',
        'Backend\Behaviors\ReorderController',
        'Backend\Behaviors\RelationController'
    ];
    
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';
    public $reorderConfig = 'config_reorder.yaml';
    public $relationConfig = 'config_relation.yaml';

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Pensoft.Courses', 'courses', 'topics');
        $this->pageTitle = 'Courses Settings';
    }
    
    public function index()
    {
        $this->asExtension('ListController')->index();
    }
    
    // AJAX handler for adding a new lesson
    public function onAddLesson()
    {
        $topicId = post('topic_id');
        $blockId = post('block_id');
        $lessonName = post('lesson_name');
        
        if (!$blockId || !$lessonName) {
            Flash::error('Block and lesson name are required');
            return;
        }
        
        $block = Block::find($blockId);
        if (!$block || $block->topic_id != $topicId) {
            Flash::error('Invalid block selected');
            return;
        }
        
        $maxOrder = Lesson::where('block_id', $blockId)->max('sort_order') ?: 0;
        
        $lesson = new Lesson();
        $lesson->name = $lessonName;
        $lesson->block_id = $blockId;
        $lesson->sort_order = $maxOrder + 1;
        $lesson->slug = str_slug($lessonName);
        $lesson->save();
        
        Flash::success('Lesson added successfully');
        
        return $this->refreshLessonsPartial();
    }
    
    // AJAX handler for adding a new material
    public function onAddMaterial()
    {
        $lessonId = post('lesson_id');
        $materialTitle = post('material_title');
        
        if (!$lessonId || !$materialTitle) {
            Flash::error('Lesson and material title are required');
            return;
        }
        
        $lesson = Lesson::find($lessonId);
        if (!$lesson) {
            Flash::error('Invalid lesson selected');
            return;
        }
        
        $maxOrder = Material::where('lesson_id', $lessonId)->max('sort_order') ?: 0;
        
        $material = new Material();
        $material->title = $materialTitle;
        $material->lesson_id = $lessonId;
        $material->sort_order = $maxOrder + 1;
        $material->save();
        
        Flash::success('Material added successfully');
        
        return $this->refreshMaterialsPartial();
    }
    
    // Helper to refresh the lessons partial
    protected function refreshLessonsPartial()
    {
        $model = $this->formGetModel();
        
        return [
            '#lessons-container' => $this->makePartial('field_lessons', ['formModel' => $model])
        ];
    }
    
    // Helper to refresh the materials partial
    protected function refreshMaterialsPartial()
    {
        $model = $this->formGetModel();
        
        return [
            '#materials-container' => $this->makePartial('field_materials', ['formModel' => $model])
        ];
    }
} 