<?php namespace Pensoft\Courses\Components;

use Cms\Classes\ComponentBase;
use Pensoft\Courses\Models\Material;
use Input;

/**
 * SearchSuggestions Component
 * 
 * Provides autocomplete suggestions for search queries based on material keywords
 * Following Single Responsibility Principle - only handles suggestion generation
 */
class SearchSuggestions extends ComponentBase
{
    /**
     * Component details
     */
    public function componentDetails()
    {
        return [
            'name'        => 'Search Suggestions',
            'description' => 'Provides autocomplete suggestions for search queries based on material keywords'
        ];
    }

    /**
     * Component properties
     */
    public function defineProperties()
    {
        return [
            'maxResults' => [
                'title'             => 'Maximum Results',
                'description'       => 'Maximum number of suggestions to return',
                'type'              => 'string',
                'default'           => '10',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'Must be a positive number'
            ],
            'minQueryLength' => [
                'title'             => 'Minimum Query Length',
                'description'       => 'Minimum number of characters before showing suggestions',
                'type'              => 'string',
                'default'           => '2',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'Must be a positive number'
            ]
        ];
    }

    /**
     * AJAX handler for getting search suggestions
     * 
     * @return array
     */
    public function onGetSuggestions()
    {
        $query = trim(input('query', ''));
        $maxResults = (int) $this->property('maxResults', 10);
        $minLength = (int) $this->property('minQueryLength', 2);

        // Validate input
        if (strlen($query) < $minLength) {
            return ['suggestions' => []];
        }

        try {
            // Get keyword suggestions from database
            $suggestions = Material::getKeywordSuggestions($query, $maxResults);
            
            return [
                'suggestions' => $suggestions,
                'query' => $query
            ];
        } catch (\Exception $e) {
            // Log error and return empty suggestions
            \Log::error('SearchSuggestions error: ' . $e->getMessage(), [
                'query' => $query,
                'trace' => $e->getTraceAsString()
            ]);
            return [
                'suggestions' => [],
                'query' => $query
            ];
        }
    }
} 