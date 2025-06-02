<?php namespace Pensoft\Courses\Models;

use Model;
use October\Rain\Database\Traits\Validation;
use October\Rain\Database\Traits\Sluggable;

/**
 * Material Model
 */
class Material extends Model
{
    use Validation;
    use Sluggable;

    /**
     * @var string table associated with the model
     */
    public $table = 'pensoft_courses_materials';

    /**
     * @var array guarded attributes aren't mass assignable
     */
    protected $guarded = ['*'];

    /**
     * @var array fillable attributes are mass assignable
     */
    protected $fillable = ['name', 'slug', 'description', 'type', 'lesson_id', 'language', 'sort_order', 'prefix', 'duration', 'keywords', 'youtube_url', 'quiz', 'video_file', 'document_file'];

    /**
     * @var array rules for validation
     */
    public $rules = [
        'name' => 'required',
        'slug' => 'required|unique:pensoft_courses_materials',
        'description' => 'nullable',
        'type' => 'required',
        'lesson_id' => 'required|exists:pensoft_courses_lessons,id',
        'language' => 'nullable',
        'sort_order' => 'integer',
        'prefix' => 'nullable',
        'duration' => 'nullable', 
        'keywords' => 'nullable',
        'youtube_url' => 'nullable|url',
        'quiz' => 'nullable',
        'video_file' => 'nullable',
        'document_file' => 'nullable'
    ];

    /**
     * @var array Attributes to be cast to native types
     */
    protected $casts = [];

    /**
     * @var array jsonable attribute names that are json encoded and decoded from the database
     */
    protected $jsonable = ['keywords'];

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
     * Material types
     */
    const TYPE_TEXT = 'text';
    const TYPE_VIDEO = 'video';
    const TYPE_DOCUMENT = 'document';
    const TYPE_QUIZ = 'quiz';

    public function getTypeOptions()
    {
        // First get options from settings
        $settingsOptions = \Pensoft\Courses\Models\Setting::getMaterialTypeOptions();
        
        // If settings are empty, fall back to hardcoded options for backwards compatibility
        if (empty($settingsOptions)) {
            return [
                self::TYPE_TEXT => 'Text',
                self::TYPE_VIDEO => 'Video',
                self::TYPE_DOCUMENT => 'Document',
                self::TYPE_QUIZ => 'Quiz'
            ];
        }
        
        return $settingsOptions;
    }

    /**
     * Returns options for language dropdown
     */
    public function getLanguageOptions()
    {
        return \Pensoft\Courses\Models\Language::getLanguageOptionsForDropdown();
    }

    /**
     * @var array hasOne and other relations
     */
    public $hasOne = [];
    public $hasMany = [];
    public $belongsTo = [
        'lesson' => [
            'Pensoft\Courses\Models\Lesson'
        ]
    ];
    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [
        'cover' => ['System\Models\File'],
        'video_file' => ['System\Models\File'],
        'document_file' => ['System\Models\File']
    ];
    public $attachMany = [];

    /**
     * Scope for getting all materials for a specific topic
     */
    public function scopeGetAllMaterialsForTopic($query, $topicId)
    {
        return $query->whereHas('lesson.block', function($q) use ($topicId) {
            $q->where('topic_id', $topicId);
        });
    }

    /**
     * Scope for hierarchical search across materials, lessons, blocks, and topics
     * 
     * This searches for materials that match the search term in:
     * - Material names and descriptions
     * - Material keywords (JSON field)
     * - Parent lesson names  
     * - Parent block names
     * - Parent topic names
     * 
     * Example: Searching "Multifunctional" returns materials where the term appears
     * in any of the above hierarchy levels.
     */
    public function scopeHierarchicalSearch($query, $searchTerm)
    {
        if (empty($searchTerm)) {
            return $query;
        }

        $searchTerm = strtolower(trim($searchTerm));
        
        return $query->where(function($q) use ($searchTerm) {
            // Search in material names and descriptions
            $q->whereRaw('LOWER(name) LIKE ?', ['%' . $searchTerm . '%'])
              ->orWhereRaw('LOWER(description) LIKE ?', ['%' . $searchTerm . '%']);
              
            // Search in keywords JSON field (PostgreSQL)
            $q->orWhereRaw('EXISTS (SELECT 1 FROM json_array_elements_text(keywords::json) AS keyword WHERE LOWER(keyword) LIKE ?)', ['%' . $searchTerm . '%']);
            
            // Search in parent lesson names
            $q->orWhereHas('lesson', function($lessonQ) use ($searchTerm) {
                $lessonQ->whereRaw('LOWER(name) LIKE ?', ['%' . $searchTerm . '%']);
            })
            
            // Search in parent block names
            ->orWhereHas('lesson.block', function($blockQ) use ($searchTerm) {
                $blockQ->whereRaw('LOWER(name) LIKE ?', ['%' . $searchTerm . '%']);
            })
            
            // Search in parent topic names
            ->orWhereHas('lesson.block.topic', function($topicQ) use ($searchTerm) {
                $topicQ->whereRaw('LOWER(name) LIKE ?', ['%' . $searchTerm . '%']);
            });
        });
    }
}
