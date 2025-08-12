<?php namespace Pensoft\Courses\Models;

use Model;
use October\Rain\Database\Traits\Validation;
use October\Rain\Database\Traits\Sluggable;
use October\Rain\Database\Traits\Sortable;

/**
 * Material Model
 */
class Material extends Model
{
    use Validation;
    use Sluggable;
    use Sortable;

    /**
     * Material types
     */
    const TYPE_INTERACTIVE_PRESENTATION = 'interactive_presentation_h5p';
    const TYPE_VIDEO_TOUR = 'video_tour';
    const TYPE_VIDEO = 'video';
    const TYPE_VIRTUAL_REALITY_TOUR = 'virtual_reality_tour';
    const TYPE_PODCAST = 'podcast';
    const TYPE_TEXTBOOK_CHAPTER = 'textbook_chapter';
    const TYPE_WORKSHEET = 'worksheet';
    const TYPE_PHOTO_GALLERY = 'photo_gallery';
    const TYPE_IMAGE = 'image';
    const TYPE_GUIDELINE = 'guideline';
    const TYPE_STANDARD_OF_PRACTICE = 'standard_of_practice';
    const TYPE_DOCUMENT = 'document';
    const TYPE_EVALUATION = 'evaluation';
    const TYPE_PRESENTATION = 'presentation';

    /**
     * Target audiences
     */
    const TARGET_AUDIENCE_ARCHITECTS = 'architects';
    const TARGET_AUDIENCE_ENGINEERS = 'engineers';
    const TARGET_AUDIENCE_ENVIRONMENTAL_EDUCATORS = 'environmental_educators';
    const TARGET_AUDIENCE_TEACHERS = 'teachers';
    const TARGET_AUDIENCE_FARMERS = 'farmers';
    const TARGET_AUDIENCE_FORESTERS = 'foresters';
    const TARGET_AUDIENCE_LANDSCAPE_GARDENERS = 'landscape_gardeners';
    const TARGET_AUDIENCE_NGOS = 'ngos';
    const TARGET_AUDIENCE_POLICYMAKERS = 'policymakers';
    const TARGET_AUDIENCE_RESTORATION_PRACTITIONERS = 'restoration_practitioners';
    const TARGET_AUDIENCE_STUDENTS = 'students';
    const TARGET_AUDIENCE_URBAN_PLANNER = 'urban_planner';
    const TARGET_AUDIENCE_LOCAL_COMMUNITY = 'local_community';
    const TARGET_AUDIENCE_INDIGENOUS_NATIVE_GROUP = 'indigenous_native_group';
    const TARGET_AUDIENCE_UNDERREPRESENTED_GROUPS = 'underrepresented_groups';

    
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
    protected $fillable = [
        'name', 'slug', 'description', 'type', 'target_audience', 'target_audiences', 'lesson_id', 'language', 'sort_order', 
        'prefix', 'duration', 'keywords', 'youtube_url', 'slideshare_url', 'quiz', 'video_file', 'document_file',
        'author', 'contact_information', 'copyright', 'link_to_other_materials', 'download_possible',
        'date_of_creation', 'date_of_version', 'date_of_upload'
    ];

    /**
     * @var array rules for validation
     */
    public $rules = [
        'name' => 'required',
        'slug' => 'required|unique:pensoft_courses_materials',
        'description' => 'required',
        'type' => 'required',
        'target_audience' => 'nullable', // Keep for backward compatibility
        'target_audiences' => 'required|array|min:1',
        'lesson_id' => 'required|exists:pensoft_courses_lessons,id',
        'language' => 'required',
        'sort_order' => 'integer',
        'prefix' => 'required',
        'duration' => 'required', 
        'keywords' => 'required',
        'youtube_url' => 'nullable|url',
        'slideshare_url' => 'nullable',
        'quiz' => 'nullable',
        'video_file' => 'nullable',
        'document_file' => 'nullable',
        'author' => 'required',
        'contact_information' => 'required|email',
        'copyright' => 'required',
        'link_to_other_materials' => 'nullable',
        'download_possible' => 'boolean',
        'date_of_creation' => 'required|date',
        'date_of_version' => 'required|date',
        'date_of_upload' => 'required|date'
    ];

    /**
     * @var array Attributes to be cast to native types
     */
    protected $casts = [];

    /**
     * @var array jsonable attribute names that are json encoded and decoded from the database
     */
    protected $jsonable = ['keywords', 'target_audiences'];

    /**
     * @var array appends attributes to the API representation of the model (ex. toArray())
     */
    protected $appends = ['cover_url'];

    /**
     * @var array hidden attributes removed from the API representation of the model (ex. toArray())
     */
    protected $hidden = [];

    /**
     * @var array dates attributes that should be mutated to dates
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'date_of_creation',
        'date_of_version',
        'date_of_upload'
    ];

    /**
     * @var array Generate slugs from these attributes
     */
    protected $slugs = ['slug' => 'name'];

    /**
     * Mutator for keywords field to handle empty values
     * Converts empty strings to NULL to avoid JSON parsing errors
     */
    public function setKeywordsAttribute($value)
    {
        // If the value is an empty string, set it to null
        if ($value === '' || $value === null) {
            $this->attributes['keywords'] = null;
        } else {
            $this->attributes['keywords'] = $value;
        }
    }

    /**
     * Mutator for target_audiences field to handle empty values
     * Converts empty strings to NULL to avoid JSON parsing errors
     */
    public function setTargetAudiencesAttribute($value)
    {
        // If the value is an empty string or empty array, set it to null
        if ($value === '' || $value === null || (is_array($value) && empty($value))) {
            $this->attributes['target_audiences'] = null;
        } else {
            $this->attributes['target_audiences'] = $value;
        }
    }

    /**
     * Accessor to get target audiences - prioritize new field over old
     */
    public function getTargetAudiencesListAttribute()
    {
        // If new field exists, use it
        if (!empty($this->target_audiences)) {
            return $this->target_audiences;
        }
        
        // Fall back to old field for backward compatibility
        if (!empty($this->target_audience)) {
            return [$this->target_audience];
        }
        
        return [];
    }

    public static function getTypeOptions()
    {
        return [
            self::TYPE_INTERACTIVE_PRESENTATION => 'Interactive Presentation (H5P)',
            self::TYPE_VIDEO_TOUR => 'Video Tour',
            self::TYPE_VIDEO => 'Video',
            self::TYPE_VIRTUAL_REALITY_TOUR => 'Virtual Reality Tour',
            self::TYPE_PODCAST => 'Podcast',
            self::TYPE_TEXTBOOK_CHAPTER => 'Textbook Chapter',
            self::TYPE_WORKSHEET => 'Worksheet',
            self::TYPE_PHOTO_GALLERY => 'Photo Gallery',
            self::TYPE_IMAGE => 'Image',
            self::TYPE_GUIDELINE => 'Guideline',
            self::TYPE_STANDARD_OF_PRACTICE => 'Standard of Practice',
            self::TYPE_DOCUMENT => 'Document',
            self::TYPE_EVALUATION => 'Evaluation',
            self::TYPE_PRESENTATION => 'Presentation'
        ];
    }

    /**
     * Returns options for language dropdown
     */
    public function getLanguageOptions()
    {
        return \Pensoft\Courses\Models\Language::getLanguageOptionsForDropdown();
    }

    /**
     * Returns options for target audience dropdown (legacy field)
     */
    public static function getTargetAudienceOptions()
    {
        return [
            self::TARGET_AUDIENCE_ARCHITECTS => 'Architects',
            self::TARGET_AUDIENCE_ENGINEERS => 'Engineers',
            self::TARGET_AUDIENCE_ENVIRONMENTAL_EDUCATORS => 'Environmental Educators',
            self::TARGET_AUDIENCE_TEACHERS => 'Teachers',
            self::TARGET_AUDIENCE_FARMERS => 'Farmers',
            self::TARGET_AUDIENCE_FORESTERS => 'Foresters',
            self::TARGET_AUDIENCE_LANDSCAPE_GARDENERS => 'Landscape Gardeners',
            self::TARGET_AUDIENCE_NGOS => 'NGOs',
            self::TARGET_AUDIENCE_POLICYMAKERS => 'Policymakers',
            self::TARGET_AUDIENCE_RESTORATION_PRACTITIONERS => 'Restoration Practitioners',
            self::TARGET_AUDIENCE_STUDENTS => 'Students',
            self::TARGET_AUDIENCE_URBAN_PLANNER => 'Urban Planner',
            self::TARGET_AUDIENCE_LOCAL_COMMUNITY => 'Local Community',
            self::TARGET_AUDIENCE_INDIGENOUS_NATIVE_GROUP => 'Indigenous / Native Group',
            self::TARGET_AUDIENCE_UNDERREPRESENTED_GROUPS => 'Underrepresented Groups'
        ];
    }

    /**
     * Returns options for target audiences checkboxlist (new field)
     */
    public function getTargetAudiencesOptions()
    {
        return self::getTargetAudienceOptions();
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
    public $attachMany = [
        'gallery' => ['System\Models\File']
    ];

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
              
            // Search in keywords JSON field
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

    /**
     * Get keyword suggestions for autocomplete functionality
     * 
     * Returns unique keywords from materials that match the search query.
     * Only returns actual keywords, sorted by frequency of use.
     * 
     * @param string $query The search query to match against
     * @param int $limit Maximum number of suggestions to return
     * @return array Array of unique keyword suggestions
     */
    public static function getKeywordSuggestions($query, $limit = 10)
    {
        if (empty($query) || strlen(trim($query)) < 2) {
            return [];
        }

        $query = strtolower(trim($query));
        
        try {
            // Get materials with keywords
            $materialsWithKeywords = self::whereNotNull('keywords')->get();
            
            if ($materialsWithKeywords->count() === 0) {
                return [];
            }
            
            // Collect all keywords
            $allKeywords = [];
            foreach ($materialsWithKeywords as $material) {
                $keywords = $material->keywords;
                
                if (is_array($keywords)) {
                    foreach ($keywords as $keyword) {
                        if (!empty(trim($keyword))) {
                            $allKeywords[] = trim($keyword);
                        }
                    }
                } elseif (is_string($keywords) && !empty($keywords)) {
                    // Handle string keywords (comma-separated or single)
                    $keywordArray = explode(',', $keywords);
                    foreach ($keywordArray as $keyword) {
                        if (!empty(trim($keyword))) {
                            $allKeywords[] = trim($keyword);
                        }
                    }
                }
            }
            
            $uniqueKeywords = array_unique($allKeywords);
            
            if (empty($uniqueKeywords)) {
                return [];
            }
            
            // Filter keywords that contain the query
            $matchingKeywords = array_filter($uniqueKeywords, function($keyword) use ($query) {
                return stripos($keyword, $query) !== false;
            });
            
            // Sort alphabetically and return results
            $matchingKeywords = array_values($matchingKeywords);
            sort($matchingKeywords);
            
            return array_slice($matchingKeywords, 0, $limit);
            
        } catch (\Exception $e) {
            \Log::error('Material keyword suggestions error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Accessor for cover URL
     * Returns the file path for the cover image if it exists
     */
    public function getCoverUrlAttribute()
    {
        return $this->cover ? $this->cover->getPath() : null;
    }

    /**
     * Accessor used by reorder UI: shows "prefix — name [Type]"
     */
    public function getReorderLabelAttribute()
    {
        $parts = [];
        if (!empty($this->prefix)) {
            $parts[] = trim($this->prefix);
        }
        if (!empty($this->name)) {
            $parts[] = trim($this->name);
        }

        $label = implode(' — ', $parts);

        if (!empty($this->type)) {
            $typeOptions = self::getTypeOptions();
            $typeLabel = $typeOptions[$this->type] ?? $this->type;
            $label .= $label ? " [{$typeLabel}]" : $typeLabel;
        }

        return $label ?: (string) $this->id;
    }
}
