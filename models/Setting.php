<?php namespace Pensoft\Courses\Models;

use Model;
use October\Rain\Database\Traits\Validation;
use October\Rain\Database\Traits\Sortable;

/**
 * Setting Model
 */
class Setting extends Model
{
    use Validation;
    use Sortable;

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
    protected $fillable = ['type', 'value', 'label', 'sort_order', 'is_active'];

    /**
     * @var array rules for validation
     */
    public $rules = [
        'type' => 'required|in:block_level,material_type',
        'value' => 'required|unique:pensoft_courses_settings,value,NULL,id,type,{{type}}',
        'label' => 'required',
        'sort_order' => 'integer',
        'is_active' => 'boolean'
    ];

    /**
     * @var array Attributes to be cast to native types
     */
    protected $casts = [
        'is_active' => 'boolean'
    ];

    /**
     * @var array jsonable attribute names that are json encoded and decoded from the database
     */
    protected $jsonable = [];

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
     * Boot the model
     */
    public static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->sort_order)) {
                $maxOrder = static::where('type', $model->type)->max('sort_order') ?: 0;
                $model->sort_order = $maxOrder + 1;
            }
        });
    }

    /**
     * Setting types
     */
    const TYPE_BLOCK_LEVEL = 'block_level';
    const TYPE_MATERIAL_TYPE = 'material_type';

    /**
     * Get type options for dropdown
     */
    public function getTypeOptions()
    {
        return [
            self::TYPE_BLOCK_LEVEL => 'Block Level',
            self::TYPE_MATERIAL_TYPE => 'Material Type'
        ];
    }

    /**
     * Get block level options
     */
    public static function getBlockLevelOptions()
    {
        return self::where('type', self::TYPE_BLOCK_LEVEL)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('label')
            ->pluck('label', 'value')
            ->toArray();
    }

    /**
     * Get material type options
     */
    public static function getMaterialTypeOptions()
    {
        return self::where('type', self::TYPE_MATERIAL_TYPE)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('label')
            ->pluck('label', 'value')
            ->toArray();
    }

    /**
     * Scope for block levels
     */
    public function scopeBlockLevels($query)
    {
        return $query->where('type', self::TYPE_BLOCK_LEVEL);
    }

    /**
     * Scope for material types
     */
    public function scopeMaterialTypes($query)
    {
        return $query->where('type', self::TYPE_MATERIAL_TYPE);
    }

    /**
     * Scope for active records
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * @var array hasOne and other relations
     */
    public $hasOne = [];
    public $hasMany = [];
    public $belongsTo = [];
    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];
} 