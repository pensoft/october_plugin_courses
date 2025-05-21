<?php namespace Pensoft\Courses\Models;

use Model;
use October\Rain\Database\Traits\Validation;
use Cache;
use RainLab\Location\Models\Country;
use Pensoft\Partners\Models\Partners;

/**
 * Setting Model
 */
class Setting extends Model
{
    use Validation;

    /**
     * @var string table associated with the model
     */
    public $table = 'pensoft_courses_settings';

    /**
     * @var array guarded attributes aren't mass assignable
     */
    protected $guarded = ['*'];

    /**
     * @var array fillable attributes are mass assignable
     */
    protected $fillable = ['key', 'value', 'group', 'description'];

    /**
     * @var array rules for validation
     */
    public $rules = [
        'key' => 'required|unique:pensoft_courses_settings',
        'group' => 'nullable',
        'value' => 'nullable',
        'description' => 'nullable'
    ];

    /**
     * @var array Attributes to be cast to native types
     */
    protected $casts = [];

    /**
     * @var array jsonable attribute names that are json encoded and decoded from the database
     */
    protected $jsonable = ['value'];

    /**
     * @var array appends attributes to the API representation of the model (ex. toArray())
     */
    protected $appends = [];

    /**
     * @var array hidden attributes removed from the API representation of the model (ex. toArray())
     */
    protected $hidden = [];

    /**
     * @var array dates attributes that should be mutated to dates
     */
    protected $dates = [
        'created_at',
        'updated_at'
    ];

    /**
     * Gets a setting value by key
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        $cacheKey = 'pensoft_courses_settings_' . $key;

        $value = Cache::remember($cacheKey, 1440, function() use ($key, $default) {
            $setting = self::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });

        return $value;
    }

    /**
     * Sets a setting value by key
     *
     * @param string $key
     * @param mixed $value
     * @param string $group
     * @param string $description
     * @return void
     */
    public static function set($key, $value, $group = null, $description = null)
    {
        $setting = self::firstOrNew(['key' => $key]);
        $setting->value = $value;
        
        if ($group) {
            $setting->group = $group;
        }
        
        if ($description) {
            $setting->description = $description;
        }
        
        $setting->save();
        
        // Clear cache
        Cache::forget('pensoft_courses_settings_' . $key);

        return $setting;
    }

    /**
     * Returns language options for dropdowns from RainLab.Location countries
     */
    public static function getLanguageOptions()
    {
        return Country::isEnabled()
            ->whereNotNull('country_language')
            ->orderBy('is_pinned', 'desc')
            ->orderBy('name', 'asc')
            ->lists('country_language', 'code');
    }
    
    /**
     * Returns levels registered in the system
     * 
     * @return array
     */
    public static function getLevels()
    {
        return self::get('levels', []);
    }
    
    /**
     * Adds a new level
     * 
     * @param string $code Level code
     * @param string $name Level name
     * @param string $description Level description
     * @return array Updated levels
     */
    public static function addLevel($code, $name, $description = '')
    {
        $levels = self::getLevels();
        $levels[$code] = [
            'name' => $name,
            'description' => $description
        ];
        
        self::set('levels', $levels, 'general', 'Available levels');
        
        return $levels;
    }
    
    /**
     * Removes a level
     * 
     * @param string $code Level code
     * @return array Updated levels
     */
    public static function removeLevel($code)
    {
        $levels = self::getLevels();
        
        if (isset($levels[$code])) {
            unset($levels[$code]);
            self::set('levels', $levels, 'general', 'Available levels');
        }
        
        return $levels;
    }
    
    /**
     * Returns level options for dropdowns
     */
    public static function getLevelOptions()
    {
        $levels = self::getLevels();
        $options = [];
        
        foreach ($levels as $code => $level) {
            $options[$code] = $level['name'];
        }
        
        return $options;
    }
    
    /**
     * Returns departments from Partners plugin
     * 
     * @return array
     */
    public static function getDepartmentOptions()
    {
        return Partners::where('type', 1)->lists('title', 'id');
    }
    
    /**
     * Returns material types registered in the system
     * 
     * @return array
     */
    public static function getMaterialTypes()
    {
        return self::get('material_types', []);
    }
    
    /**
     * Adds a new material type
     * 
     * @param string $code Type code
     * @param string $name Type name
     * @param string $description Type description
     * @return array Updated material types
     */
    public static function addMaterialType($code, $name, $description = '')
    {
        $types = self::getMaterialTypes();
        $types[$code] = [
            'name' => $name,
            'description' => $description
        ];
        
        self::set('material_types', $types, 'materials', 'Available material types');
        
        return $types;
    }
    
    /**
     * Removes a material type
     * 
     * @param string $code Type code
     * @return array Updated material types
     */
    public static function removeMaterialType($code)
    {
        $types = self::getMaterialTypes();
        
        if (isset($types[$code])) {
            unset($types[$code]);
            self::set('material_types', $types, 'materials', 'Available material types');
        }
        
        return $types;
    }
    
    /**
     * Returns material type options for dropdowns
     */
    public static function getMaterialTypeOptions()
    {
        $types = self::getMaterialTypes();
        $options = [];
        
        foreach ($types as $code => $type) {
            $options[$code] = $type['name'];
        }
        
        return $options;
    }

    /**
     * Returns dropdown options for groups
     * 
     * @return array
     */
    public static function getGroupOptions()
    {
        return self::distinct()->orderBy('group')->pluck('group', 'group')->toArray();
    }
} 