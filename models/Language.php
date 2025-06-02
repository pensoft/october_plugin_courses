<?php namespace Pensoft\Courses\Models;

use RainLab\Location\Models\Country;

/**
 * Language Service Class
 * Centralizes language options for the entire plugin
 */
class Language
{
    /**
     * Get all available language options including custom languages
     * 
     * @return array
     */
    public static function getLanguageOptions()
    {
        // Get enabled countries with language codes
        $countryOptions = Country::whereNotNull('country_language')
            ->where('country_language', '!=', '')
            ->where('is_enabled', true)
            ->distinct()
            ->orderBy('country_language')
            ->pluck('country_language', 'country_language')
            ->toArray();
        
        // Add custom language options
        $customOptions = [
            'Valencian' => 'Valencian'
        ];
        
        // Merge and sort alphabetically
        $allOptions = array_merge($countryOptions, $customOptions);
        asort($allOptions);
        
        return $allOptions;
    }

    /**
     * Get language options for dropdowns (with proper language names)
     * Used in backend forms
     * 
     * @return array
     */
    public static function getLanguageOptionsForDropdown()
    {
        // Get enabled countries with their language codes and names
        $countryOptions = Country::whereNotNull('country_language')
            ->where('country_language', '!=', '')
            ->where('is_enabled', true)
            ->orderBy('is_pinned', 'desc')
            ->orderBy('country_language', 'asc')
            ->pluck('country_language', 'country_language')
            ->toArray();
        
        // Add custom language options
        $customOptions = [
            'Valencian' => 'Valencian'
        ];
        
        // Merge and sort alphabetically
        $allOptions = array_merge($countryOptions, $customOptions);
        asort($allOptions);
        
        return $allOptions;
    }

    /**
     * Check if a language is a custom language
     * 
     * @param string $language
     * @return bool
     */
    public static function isCustomLanguage($language)
    {
        $customLanguages = ['Valencian'];
        return in_array($language, $customLanguages);
    }

    /**
     * Get the display name for a language
     * 
     * @param string $languageCode
     * @return string
     */
    public static function getLanguageDisplayName($languageCode)
    {
        // Handle custom languages
        if ($languageCode === 'Valencian') {
            return 'Valencian';
        }

        // For standard languages, the language code is already the display name
        // Since we're now storing the actual language name in the language field
        return $languageCode;
    }

    /**
     * Get featured/pinned language options for hero section
     * 
     * @return array
     */
    public static function getFeaturedLanguageOptions()
    {
        // Get pinned countries with language codes
        $countryOptions = Country::whereNotNull('country_language')
            ->where('country_language', '!=', '')
            ->where('is_enabled', true)
            ->where('is_pinned', true)
            ->distinct()
            ->orderBy('country_language')
            ->pluck('country_language', 'country_language')
            ->toArray();
        
        // Add custom language options (always featured)
        $customOptions = [
            'Valencian' => 'Valencian'
        ];
        
        // Merge and sort alphabetically
        $allOptions = array_merge($countryOptions, $customOptions);
        asort($allOptions);
        
        return $allOptions;
    }
} 