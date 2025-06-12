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
            'pensoft.courses.access_settings' => [
                'tab' => 'Courses',
                'label' => 'Manage course settings'
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
                    ],
                    'settings' => [
                        'label'       => 'Settings',
                        'url'         => Backend::url('pensoft/courses/settings'),
                        'icon'        => 'icon-cog',
                        'permissions' => ['pensoft.courses.access_settings'],
                        'sideMenu' => [
                            'general' => [
                                'label'       => 'Overview',
                                'url'         => Backend::url('pensoft/courses/settings'),
                                'icon'        => 'icon-dashboard',
                                'permissions' => ['pensoft.courses.access_settings'],
                            ],
                            'levels' => [
                                'label'       => 'Levels',
                                'url'         => Backend::url('pensoft/courses/settings/levels'),
                                'icon'        => 'icon-signal',
                                'permissions' => ['pensoft.courses.access_settings'],
                            ],
                            'materialtypes' => [
                                'label'       => 'Material Types',
                                'url'         => Backend::url('pensoft/courses/settings/materialtypes'),
                                'icon'        => 'icon-file-text-o',
                                'permissions' => ['pensoft.courses.access_settings'],
                            ],
                        ]
                    ]
                ]
            ],
        ];
    }
}
