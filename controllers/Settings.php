<?php namespace Pensoft\Courses\Controllers;

use BackendMenu;
use Backend\Classes\Controller;
use Backend\Facades\Backend;
use Pensoft\Courses\Models\Setting;
use Flash;

/**
 * Settings Back-end Controller
 */
class Settings extends Controller
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

        BackendMenu::setContext('Pensoft.Courses', 'courses', 'settings');
    }

    /**
     * Show overview page
     */
    public function index()
    {
        $this->pageTitle = 'Settings Overview';
        $this->bodyClass = 'compact-container';
        
        $this->vars['blockLevelsCount'] = Setting::blockLevels()->active()->count();
        $this->vars['materialTypesCount'] = Setting::materialTypes()->active()->count();
        $this->vars['totalSettingsCount'] = Setting::active()->count();
    }

    /**
     * Show block levels page
     */
    public function levels()
    {
        $this->pageTitle = 'Manage Block Levels';
        $this->bodyClass = 'compact-container';
        $this->listConfig = 'config_list_levels.yaml';
        return $this->asExtension('ListController')->index();
    }

    /**
     * Extend the list query for levels page
     */
    public function listExtendQuery($query)
    {
        $action = $this->action;
        
        if ($action == 'levels') {
            $query->where('type', Setting::TYPE_BLOCK_LEVEL);
        } elseif ($action == 'materialtypes') {
            $query->where('type', Setting::TYPE_MATERIAL_TYPE);
        }
        
        return $query;
    }

    /**
     * Show material types page
     */
    public function materialtypes()
    {
        $this->pageTitle = 'Manage Material Types';
        $this->bodyClass = 'compact-container';
        $this->listConfig = 'config_list_materialtypes.yaml';
        return $this->asExtension('ListController')->index();
    }

    /**
     * Create a new block level
     */
    public function createLevel()
    {
        $this->pageTitle = 'Create Block Level';
        $this->bodyClass = 'compact-container';
        
        // Pre-populate the type field
        $this->vars['settingType'] = Setting::TYPE_BLOCK_LEVEL;
        $this->vars['backUrl'] = Backend::url('pensoft/courses/settings/levels');
        
        $this->asExtension('FormController')->create();
    }

    /**
     * Create a new material type
     */
    public function createMaterialType()
    {
        $this->pageTitle = 'Create Material Type';
        $this->bodyClass = 'compact-container';
        
        // Pre-populate the type field
        $this->vars['settingType'] = Setting::TYPE_MATERIAL_TYPE;
        $this->vars['backUrl'] = Backend::url('pensoft/courses/settings/materialtypes');
        
        $this->asExtension('FormController')->create();
    }

    /**
     * Form event handler to pre-populate fields when creating
     */
    public function formExtendModel($model)
    {
        // Set the type based on the query parameter
        if (get('type') == 'blocklevel') {
            $model->type = Setting::TYPE_BLOCK_LEVEL;
        } elseif (get('type') == 'materialtype') {
            $model->type = Setting::TYPE_MATERIAL_TYPE;
        }
        return $model;
    }

    /**
     * Handle successful form save
     */
    public function formAfterSave($model)
    {
        // Redirect based on the type after successful save
        if ($model->type == Setting::TYPE_BLOCK_LEVEL) {
            Flash::success('Block Level created successfully!');
            return redirect(Backend::url('pensoft/courses/settings/levels'));
        } elseif ($model->type == Setting::TYPE_MATERIAL_TYPE) {
            Flash::success('Material Type created successfully!');
            return redirect(Backend::url('pensoft/courses/settings/materialtypes'));
        }
    }

    /**
     * Handle "Save and Close" button for create actions
     */
    public function onSaveAndClose()
    {
        // Determine the redirect URL based on the current action
        $redirectUrl = 'pensoft/courses/settings';
        
        if ($this->action == 'createLevel') {
            $redirectUrl = 'pensoft/courses/settings/levels';
        } elseif ($this->action == 'createMaterialType') {
            $redirectUrl = 'pensoft/courses/settings/materialtypes';
        }
        
        // Call the parent save method
        $result = $this->asExtension('FormController')->create_onSave();
        
        // If save was successful, redirect to the appropriate list
        if ($result) {
            return redirect(Backend::url($redirectUrl));
        }
        
        return $result;
    }

    /**
     * Update a setting
     */
    public function update($recordId = null)
    {
        $setting = Setting::find($recordId);
        
        if ($setting) {
            if ($setting->type == Setting::TYPE_BLOCK_LEVEL) {
                $this->pageTitle = 'Edit Block Level';
                $this->vars['backUrl'] = Backend::url('pensoft/courses/settings/levels');
            } else {
                $this->pageTitle = 'Edit Material Type';
                $this->vars['backUrl'] = Backend::url('pensoft/courses/settings/materialtypes');
            }
        }
        
        $this->bodyClass = 'compact-container';
        $this->asExtension('FormController')->update($recordId);
    }

    /**
     * Preview a setting (readonly view)
     */
    public function preview($recordId = null)
    {
        $setting = Setting::find($recordId);
        
        if ($setting) {
            if ($setting->type == Setting::TYPE_BLOCK_LEVEL) {
                $this->pageTitle = 'View Block Level';
                $this->vars['backUrl'] = Backend::url('pensoft/courses/settings/levels');
            } else {
                $this->pageTitle = 'View Material Type';
                $this->vars['backUrl'] = Backend::url('pensoft/courses/settings/materialtypes');
            }
        }
        
        $this->bodyClass = 'compact-container';
        $this->asExtension('FormController')->preview($recordId);
    }

    /**
     * Delete selected settings
     */
    public function onDelete()
    {
        if (($checkedIds = post('checked')) && is_array($checkedIds) && count($checkedIds)) {
            foreach ($checkedIds as $recordId) {
                if (!$record = Setting::find($recordId)) {
                    continue;
                }
                $record->delete();
            }
            
            Flash::success('Successfully deleted selected settings.');
        }
        
        return $this->listRefresh();
    }

    /**
     * Reorder block levels
     */
    public function reorderLevels()
    {
        $this->pageTitle = 'Reorder Block Levels';
        $this->bodyClass = 'compact-container';
        
        // Override reorder config for this action
        $this->reorderConfig = 'config_reorder_levels.yaml';
        
        // Initialize the reorder behavior
        $this->asExtension('ReorderController')->reorder();
    }

    /**
     * Reorder material types
     */
    public function reorderMaterialTypes()
    {
        $this->pageTitle = 'Reorder Material Types';
        $this->bodyClass = 'compact-container';
        
        // Override reorder config for this action
        $this->reorderConfig = 'config_reorder_materialtypes.yaml';
        
        // Initialize the reorder behavior
        $this->asExtension('ReorderController')->reorder();
    }

    /**
     * Filter reorder query based on the action
     */
    public function reorderExtendQuery($query)
    {
        $action = $this->action;
        
        if ($action == 'reorderLevels') {
            $query->where('type', Setting::TYPE_BLOCK_LEVEL);
        } elseif ($action == 'reorderMaterialTypes') {
            $query->where('type', Setting::TYPE_MATERIAL_TYPE);
        }
        
        return $query;
    }

    public function create()
    {
        $type = get('type');
        if ($type == 'blocklevel') {
            $this->vars['backUrl'] = Backend::url('pensoft/courses/settings/levels');
        } elseif ($type == 'materialtype') {
            $this->vars['backUrl'] = Backend::url('pensoft/courses/settings/materialtypes');
        } else {
            $this->vars['backUrl'] = Backend::url('pensoft/courses/settings');
        }
        return $this->asExtension('FormController')->create();
    }
} 