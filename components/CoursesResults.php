<?php namespace Pensoft\Courses\Components;

use Cms\Classes\ComponentBase;
use Pensoft\Courses\Models\Topic;
use Pensoft\Courses\Models\Block;
use Pensoft\Courses\Models\Material;
use Pensoft\Courses\Models\Language;
use Pensoft\Partners\Models\Partners;
use RainLab\Location\Models\Country;
use Input;

class CoursesResults extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'Courses Results',
            'description' => 'Handles courses search results and filtering'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function onRun()
    {
        $this->prepareVars();
    }

    protected function prepareVars()
    {
        $this->page['results'] = null;
        $this->page['groupedResults'] = [];
        
        // Get filter options dynamically
        $this->page['topics'] = Topic::all();
        
        // Add filter data for the reusable component
        $this->page['blockLevels'] = Block::getLevelOptions();
        $this->page['materialTypes'] = Material::getTypeOptions();
        
        // Languages including custom languages like Valencian
        $this->page['languages'] = Language::getLanguageOptions();
        
        // Departments from Partners where type = 1
        $this->page['departments'] = Partners::where('type', 1)
            ->whereNotNull('instituion')
            ->where('instituion', '!=', '')
            ->distinct()
            ->orderBy('instituion')
            ->pluck('instituion', 'instituion')
            ->toArray();
    }

    public function onLoadResources()
    {
        $language = input('language');
        $level = input('level');
        $department = input('department');
        $type = input('type');
        $topic = input('topic');
        $search = input('search') ?: input('q');
        $page = input('page', 1);
        $perPage = input('per_page', 15);
        
        try {
            // Build query directly using Material model
            $query = Material::with(['lesson', 'lesson.block', 'lesson.block.topic', 'cover']);
            
            // Add a basic where clause to ensure we only get materials with valid lessons
            $query->whereHas('lesson');
            
            // Apply filters only if they have actual values
            if ($language && $language !== '') {
                $query->where('language', $language);
            }
            
            if ($level && $level !== '') {
                $query->whereHas('lesson.block', function($q) use ($level) {
                    $q->where('level', $level);
                });
            }
            
            if ($department && $department !== '') {
                // Filter by topic's institution field with flexible matching
                $query->whereHas('lesson.block.topic', function($q) use ($department) {
                    $q->where(function($subQ) use ($department) {
                        // Try exact match first
                        $subQ->where('institution', '=', $department)
                        // Try case-insensitive match
                        ->orWhereRaw('LOWER(TRIM(institution)) = LOWER(TRIM(?))', [$department])
                        // Try partial match in case of extra text
                        ->orWhereRaw('LOWER(institution) LIKE LOWER(?)', ['%' . $department . '%'])
                        // Try if institution field contains partner ID and we need to join
                        ->orWhereExists(function($existsQ) use ($department) {
                            $existsQ->select(\DB::raw(1))
                                ->from('pensoft_partners_partners as p')
                                ->whereRaw('p.id::text = pensoft_courses_topics.institution')
                                ->where('p.instituion', $department);
                        });
                    });
                });
            }
            
            if ($type && $type !== '') {
                $query->where('type', $type);
            }
            
            if ($topic && $topic !== '') {
                $topic = urldecode($topic);
                
                $query->whereHas('lesson.block.topic', function($q) use ($topic) {
                    $q->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($topic) . '%']);
                });
            }
            
            if ($search && $search !== '') {
                // Use the hierarchical search scope from Material model
                $query->hierarchicalSearch($search);
            }
            
            // Get paginated results
            $materials = $query->paginate($perPage);

            
            // Add computed cover paths to the materials
            foreach ($materials->items() as $material) {
                if ($material->cover) {
                    $material->cover_url = $material->cover->getPath();
                }
            }
            
            // Convert to array format that matches the original API response
            $results = [
                'data' => $materials->items(),
                'meta' => [
                    'pagination' => [
                        'total' => $materials->total(),
                        'per_page' => $materials->perPage(),
                        'current_page' => $materials->currentPage(),
                        'last_page' => $materials->lastPage(),
                        'from' => $materials->firstItem(),
                        'to' => $materials->lastItem()
                    ]
                ]
            ];
            
            // Group materials by topic and blocks
            $groupedResults = $this->groupMaterialsByTopicAndBlocks($results);
            
            $this->page['results'] = $results;
            $this->page['groupedResults'] = $groupedResults;
            
            return [
                '#results-container' => $this->renderPartial('components/grouped-resource-results', ['groupedResults' => $groupedResults]),
                '#pagination-container' => $this->renderPartial('components/resource-pagination', ['pagination' => $results['meta']['pagination'] ?? null])
            ];
        } catch (\Exception $e) {
            // Log the technical error details for debugging
            \Log::error('CoursesResults filtering error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'filters' => [
                    'language' => $language,
                    'level' => $level,
                    'department' => $department,
                    'type' => $type,
                    'topic' => $topic,
                    'search' => $search,
                    'page' => $page,
                    'perPage' => $perPage
                ],
                'user_agent' => request()->header('User-Agent'),
                'ip' => request()->ip()
            ]);
            
            // Return user-friendly error message
            return [
                '#results-container' => '<div class="error-message"><p>We\'re sorry, but there was an issue loading the search results. Please try again or adjust your filters.</p></div>',
                '#pagination-container' => ''
            ];
        }
    }

    protected function groupMaterialsByTopicAndBlocks($results)
    {
        $grouped = [];
        
        // Always return grouped structure, even if empty
        if (!isset($results['data']) || !is_array($results['data']) || empty($results['data'])) {
            return $grouped;
        }
        
        foreach ($results['data'] as $material) {
            // Get topic information from material
            $topicName = 'Unknown Topic';
            $topicSlug = 'unknown';
            $blockName = 'Unknown Block';
            $blockId = 'unknown';
            
            // Extract topic and block info from material relationships
            if (isset($material['lesson']['block']['topic'])) {
                $topic = $material['lesson']['block']['topic'];
                $topicName = $topic['name'] ?? 'Unknown Topic';
                $topicSlug = $topic['slug'] ?? 'unknown';
            }
            
            if (isset($material['lesson']['block'])) {
                $block = $material['lesson']['block'];
                $blockName = $block['name'] ?? 'Unknown Block';
                $blockId = $block['id'] ?? 'unknown';
            }
            
            // Initialize topic if not exists
            if (!isset($grouped[$topicSlug])) {
                $grouped[$topicSlug] = [
                    'name' => $topicName,
                    'slug' => $topicSlug,
                    'blocks' => []
                ];
            }
            
            // Initialize block if not exists
            if (!isset($grouped[$topicSlug]['blocks'][$blockId])) {
                $grouped[$topicSlug]['blocks'][$blockId] = [
                    'name' => $blockName,
                    'id' => $blockId,
                    'materials' => []
                ];
            }
            
            // Add material to the block
            $grouped[$topicSlug]['blocks'][$blockId]['materials'][] = $material;
        }
        
        return $grouped;
    }

    public function onApplyFilters()
    {
        return $this->onLoadResources();
    }
} 