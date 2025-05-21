<?php namespace Pensoft\Courses\Controllers;

use BackendMenu;
use Backend\Classes\Controller;
use Flash;
use Pensoft\Courses\Models\Setting;
use RainLab\Location\Models\Country;
use Input;

/**
 * Settings Backend Controller
 */
class Settings extends Controller
{
    public $implement = [
        \Backend\Behaviors\ListController::class
    ];

    /**
     * @var string listConfig file
     */
    public $listConfig = 'config_list.yaml';

    /**
     * __construct the controller
     */
    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Pensoft.Courses', 'courses', 'settings');
    }

    /**
     * Index action - main settings overview
     */
    public function index()
    {
        $this->pageTitle = 'Settings';
        
        $this->vars['languages'] = Country::isEnabled()->whereNotNull('country_language')->count();
        $this->vars['levels'] = Setting::getLevelOptions();
        $this->vars['departments'] = Setting::getDepartmentOptions();
        $this->vars['materialTypes'] = Setting::getMaterialTypeOptions();
    }
    
    /**
     * Level management
     */
    public function levels()
    {
        $this->pageTitle = 'Level Settings';
        $this->vars['levels'] = Setting::getLevels();
    }
    
    /**
     * Add a new level
     */
    public function onAddLevel()
    {
        $data = post();
        
        if (empty($data['code']) || empty($data['name'])) {
            Flash::error('Please provide all required fields.');
            return;
        }
        
        Setting::addLevel($data['code'], $data['name'], $data['description'] ?? '');
        Flash::success('Level has been added successfully.');
        
        return $this->refreshLevelsList();
    }
    
    /**
     * Remove a level
     */
    public function onRemoveLevel()
    {
        $code = post('code');
        
        if (empty($code)) {
            Flash::error('Level code is required.');
            return;
        }
        
        Setting::removeLevel($code);
        Flash::success('Level has been removed successfully.');
        
        return $this->refreshLevelsList();
    }
    
    /**
     * Refresh levels list partial
     */
    protected function refreshLevelsList()
    {
        $this->vars['levels'] = Setting::getLevels();
        return [
            '#level-list' => $this->makePartial('level_list')
        ];
    }
    
    /**
     * Material types management
     */
    public function materialtypes()
    {
        $this->pageTitle = 'Material Type Settings';
        $this->vars['materialTypes'] = Setting::getMaterialTypes();
    }
    
    /**
     * Add a new material type
     */
    public function onAddMaterialType()
    {
        $data = post();
        
        if (empty($data['code']) || empty($data['name'])) {
            Flash::error('Please provide all required fields.');
            return;
        }
        
        Setting::addMaterialType($data['code'], $data['name'], $data['description'] ?? '');
        Flash::success('Material type has been added successfully.');
        
        return $this->refreshMaterialTypesList();
    }
    
    /**
     * Remove a material type
     */
    public function onRemoveMaterialType()
    {
        $code = post('code');
        trace_log('Removing material type with code: ' . $code);
        
        if (empty($code)) {
            Flash::error('Material type code is required.');
            trace_log('Error: Material type code is empty');
            return;
        }
        
        $types = Setting::getMaterialTypes();
        trace_log('Current material types: ' . json_encode($types));
        
        Setting::removeMaterialType($code);
        Flash::success('Material type has been removed successfully.');
        
        $updatedTypes = Setting::getMaterialTypes();
        trace_log('Updated material types: ' . json_encode($updatedTypes));
        
        return $this->refreshMaterialTypesList();
    }
    
    /**
     * Refresh material types list partial
     */
    protected function refreshMaterialTypesList()
    {
        $this->vars['materialTypes'] = Setting::getMaterialTypes();
        return [
            '#material-type-list' => $this->makePartial('material_type_list')
        ];
    }
} 