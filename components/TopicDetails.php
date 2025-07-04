<?php namespace Pensoft\Courses\Components;

use Cms\Classes\ComponentBase;
use Pensoft\Courses\Models\Topic;
use Pensoft\Courses\Models\Block;
use Pensoft\Courses\Models\Material;
use Pensoft\Courses\Models\Language;
use Pensoft\Partners\Models\Partners;
use RainLab\Location\Models\Country;

class TopicDetails extends ComponentBase
{
    public $topic;
    public $nextTopic;
    public $prevTopic;
    public $topicInstitutions;

    public function componentDetails()
    {
        return [
            'name'        => 'Topic Details',
            'description' => 'Handles topic page display and filtering'
        ];
    }

    public function defineProperties()
    {
        return [
            'slug' => [
                'title'       => 'Topic Slug',
                'description' => 'The slug parameter for the topic',
                'type'        => 'string',
                'default'     => '{{ :slug }}'
            ]
        ];
    }

    public function onRun()
    {
        $this->prepareVars();
    }

    protected function prepareVars()
    {
        $slug = $this->property('slug');
        
        // Find the topic by slug with all related data including partner
        $this->topic = Topic::with(['blocks.lessons.materials', 'partner'])
            ->where('slug', $slug)
            ->first();
        
        if (!$this->topic) {
            return $this->controller->run('404');
        }
        
        // Sort materials within each block by prefix
        $this->sortMaterialsByPrefix($this->topic->blocks);
        
        // Get next and previous topics for navigation
        $this->nextTopic = Topic::where('id', '>', $this->topic->id)->orderBy('id', 'asc')->first();
        $this->prevTopic = Topic::where('id', '<', $this->topic->id)->orderBy('id', 'desc')->first();
        
        // Get filter options dynamically
        $this->page['blockLevels'] = Block::getLevelOptions();
        $this->page['materialTypes'] = Material::getTypeOptions();
        
        // Languages including custom languages like Valencian
        $this->page['languages'] = Language::getLanguageOptions();
        
        // Get institutions for this topic from the partner relationship
        $topicInstitutions = [];
        if ($this->topic && $this->topic->partner && $this->topic->partner->instituion) {
            $topicInstitutions[] = $this->topic->partner->instituion;
        }
        
        $this->topicInstitutions = array_unique($topicInstitutions);
        
        // Set page variables
        $this->page['topic'] = $this->topic;
        $this->page['nextTopic'] = $this->nextTopic;
        $this->page['prevTopic'] = $this->prevTopic;
        $this->page['topicInstitutions'] = $this->topicInstitutions;
        $this->page['title'] = $this->topic->name;
    }

    public function onLoadFilteredBlocks()
    {
        $slug = $this->property('slug');
        $topic = Topic::where('slug', $slug)->first();
        
        if (!$topic) {
            return;
        }
        
        // Get filter parameters
        $language = post('language');
        $level = post('level'); 
        $type = post('type');
        
        // Start with all blocks for this topic
        $query = $topic->blocks()->with(['lessons.materials']);
        
        // Apply filters
        if ($level) {
            $query->where('level', $level);
        }
        
        if ($language) {
            $query->whereHas('lessons.materials', function($q) use ($language) {
                $q->where('language', $language);
            });
        }
        
        if ($type) {
            $query->whereHas('lessons.materials', function($q) use ($type) {
                $q->where('type', $type);
            });
        }
        
        $blocks = $query->get();
        
        // Sort materials within each block by prefix
        $this->sortMaterialsByPrefix($blocks);
        
        return [
            '#blocks-container' => $this->renderPartial('blocks-list', ['blocks' => $blocks])
        ];
    }

    /**
     * Sort materials within blocks by their prefix using version_compare
     * This ensures proper ordering like 1.1 > 1.2 > 1.12 instead of alphabetical
     */
    protected function sortMaterialsByPrefix($blocks)
    {
        foreach ($blocks as $block) {
            foreach ($block->lessons as $lesson) {
                if ($lesson->materials && $lesson->materials->count() > 0) {
                    $materialsArray = $lesson->materials->all();
                    
                    usort($materialsArray, function($a, $b) {
                        $prefixA = $a->prefix ?? '';
                        $prefixB = $b->prefix ?? '';
                        
                        // Handle empty prefixes - put them at the end
                        if (empty($prefixA) && empty($prefixB)) {
                            return 0;
                        }
                        if (empty($prefixA)) {
                            return 1;
                        }
                        if (empty($prefixB)) {
                            return -1;
                        }
                        
                        // Use version_compare for natural sorting of version-like strings
                        // This will properly sort 1.1, 1.2, 1.12, etc.
                        return version_compare($prefixA, $prefixB);
                    });
                    
                    // Replace the collection with sorted array
                    $lesson->setRelation('materials', collect($materialsArray));
                }
            }
        }
    }
} 