<?php namespace Pensoft\Courses\Models;

use Model;
use October\Rain\Database\Traits\Validation;
use October\Rain\Database\Traits\Sluggable;
use October\Rain\Database\Traits\Sortable;

/**
 * Block Model
 */
class Block extends Model
{
    use Validation;
    use Sluggable;
    use Sortable;

    /**
     * Block levels
     */
    const LEVEL_BASIC = 'basic';
    const LEVEL_ADVANCED = 'advanced';

    /**
     * @var string table associated with the model
     */
    public $table = 'pensoft_courses_blocks';

    /**
     * @var array guarded attributes aren't mass assignable
     */
    protected $guarded = ['*'];

    /**
     * @var array fillable attributes are mass assignable
     */
    protected $fillable = ['name', 'slug', 'topic_id', 'sort_order', 'level'];

    /**
     * @var array rules for validation
     */
    public $rules = [
        'name' => 'required',
        'slug' => 'required|unique:pensoft_courses_blocks',
        'topic_id' => 'required|exists:pensoft_courses_topics,id',
        'level' => 'nullable',
        'sort_order' => 'integer'
    ];

    /**
     * @var array Attributes to be cast to native types
     */
    protected $casts = [];

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
     * @var array Generate slugs from these attributes
     */
    protected $slugs = ['slug' => 'name'];

    /**
     * @var array hasOne and other relations
     */
    public $hasOne = [];
    public $hasMany = [
        'lessons' => [
            'Pensoft\Courses\Models\Lesson'
        ]
    ];
    public $belongsTo = [
        'topic' => [
            'Pensoft\Courses\Models\Topic'
        ]
    ];
    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];

    /**
     * Get level options for dropdown
     */
    public static function getLevelOptions()
    {
        return [
            self::LEVEL_BASIC => 'Basic',
            self::LEVEL_ADVANCED => 'Advanced'
        ];
    }

    /**
     * Scope for searching blocks by name
     */
    public function scopeSearch($query, $searchTerm)
    {
        if (empty($searchTerm)) {
            return $query;
        }

        $searchTerm = strtolower(trim($searchTerm));
        
        return $query->whereRaw('LOWER(name) LIKE ?', ['%' . $searchTerm . '%']);
    }
}
