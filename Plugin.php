<?php namespace Pensoft\Courses;

use Backend;
use System\Classes\PluginBase;
use Route;

/**
 * Courses Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'Courses',
            'description' => 'Course management system for educational content',
            'author'      => 'Pensoft',
            'icon'        => 'icon-graduation-cap'
        ];
    }

    /**
     * Register method, called when the plugin is first registered.
     *
     * @return void
     */
    public function register()
    {

    }

    /**
     * Boot method, called right before the request route.
     *
     * @return void
     */
    public function boot()
    {
        // Register download routes with CSRF protection
        Route::post('api/courses/download-gallery', 'Pensoft\Courses\Controllers\Downloads@downloadGallery')
            ->middleware('web');
        Route::post('api/courses/download-block', 'Pensoft\Courses\Controllers\Downloads@downloadBlock')
            ->middleware('web');
    }

    /**
     * Plugin dependencies
     *
     * @return array
     */
    public function pluginDependencies()
    {
        return [
            'RainLab.Location',
            'Pensoft.Partners'
        ];
    }

    /**
     * Registers any front-end components implemented in this plugin.
     *
     * @return array
     */
    public function registerComponents()
    {
        return [
            'Pensoft\Courses\Components\CoursesResults' => 'coursesResults',
            'Pensoft\Courses\Components\TopicDetails' => 'topicDetails',
            'Pensoft\Courses\Components\SearchSuggestions' => 'searchSuggestions',
        ];
    }

    /**
     * Registers any back-end permissions used by this plugin.
     *
     * @return array
     */
    public function registerPermissions()
    {
        return [
            'pensoft.courses.access_topics' => [
                'tab' => 'Courses',
                'label' => 'Manage course topics'
            ],
            'pensoft.courses.access_blocks' => [
                'tab' => 'Courses',
                'label' => 'Manage course blocks'
            ],
            'pensoft.courses.access_lessons' => [
                'tab' => 'Courses',
                'label' => 'Manage course lessons'
            ],
            'pensoft.courses.access_materials' => [
                'tab' => 'Courses',
                'label' => 'Manage course materials'
            ],
        ];
    }

    /**
     * Registers back-end navigation items for this plugin.
     *
     * @return array
     */
    public function registerNavigation()
    {
        return [
            'courses' => [
                'label'       => 'Courses',
                'url'         => Backend::url('pensoft/courses/topics'),
                'icon'        => 'icon-graduation-cap',
                'permissions' => ['pensoft.courses.*'],
                'order'       => 500,
                'sideMenu' => [
                    'topics' => [
                        'label'       => 'Topics',
                        'url'         => Backend::url('pensoft/courses/topics'),
                        'icon'        => 'icon-sitemap',
                        'permissions' => ['pensoft.courses.access_topics'],
                    ],
                    'blocks' => [
                        'label'       => 'Blocks',
                        'url'         => Backend::url('pensoft/courses/blocks'),
                        'icon'        => 'icon-th-large',
                        'permissions' => ['pensoft.courses.access_blocks'],
                    ],
                    'lessons' => [
                        'label'       => 'Lessons & Materials',
                        'url'         => Backend::url('pensoft/courses/lessons'),
                        'icon'        => 'icon-book',
                        'permissions' => ['pensoft.courses.access_lessons'],
                    ]
                ]
            ],
        ];
    }
}
