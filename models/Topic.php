<?php namespace Pensoft\Courses\Models;

use Model;
use October\Rain\Database\Traits\Validation;
use October\Rain\Database\Traits\Sluggable;
use October\Rain\Database\Traits\Sortable;
use RainLab\Location\Models\Country;
use Pensoft\Partners\Models\Partners;

/**
 * Topic Model
 */
class Topic extends Model
{
    use Validation;
    use Sluggable;
    use Sortable;

    /**
     * @var string table associated with the model
     */
    public $table = 'pensoft_courses_topics';

    /**
     * @var array guarded attributes aren't mass assignable
     */
    protected $guarded = ['*'];

    /**
     * @var array fillable attributes are mass assignable
     */
    protected $fillable = ['name', 'slug', 'language', 'sort_order', 'institution'];

    /**
     * @var array rules for validation
     */
    public $rules = [
        'name' => 'required',
        'slug' => 'required|unique:pensoft_courses_topics',
        'language' => 'nullable',
        'sort_order' => 'integer',
        'institution' => 'nullable'
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
        'blocks' => [
            'Pensoft\Courses\Models\Block'
        ]
    ];
    
    public $hasManyThrough = [
        'lessons' => [
            'Pensoft\Courses\Models\Lesson',
            'through' => 'Pensoft\Courses\Models\Block'
        ]
    ];

    public $belongsTo = [
        'partner' => [
            'Pensoft\Partners\Models\Partners',
            'key' => 'institution',
            'otherKey' => 'id'
        ]
    ];

    public $belongsToMany = [];

    public $morphTo = [];

    public $morphOne = [];

    public $morphMany = [];

    public $attachOne = [];

    public $attachMany = [];

    /**
     * Returns options for language dropdown
     */
    public function getLanguageOptions()
    {
        return \Pensoft\Courses\Models\Language::getLanguageOptionsForDropdown();
    }

    /**
     * Returns options for institution dropdown
     */
    public function getInstitutionOptions()
    {
        return Partners::whereNotNull('instituion')
            ->where('instituion', '!=', '')
            ->where('type', 1)
            ->orderBy('instituion', 'asc')
            ->lists('instituion', 'id');
    }

    /**
     * Scope for searching topics by name
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
