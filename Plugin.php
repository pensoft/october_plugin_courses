<?php namespace Pensoft\Courses;

use Backend;
use System\Classes\PluginBase;

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
     * Registers any front-end components implemented in this plugin.
     *
     * @return array
     */
    public function registerComponents()
    {
        return [];
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
                'url'         => Backend::url('pensoft/courses/topic'),
                'icon'        => 'icon-graduation-cap',
                'permissions' => ['pensoft.courses.*'],
                'order'       => 500,
                'sideMenu' => [
                    'topics' => [
                        'label'       => 'Topics',
                        'url'         => Backend::url('pensoft/courses/topic'),
                        'icon'        => 'icon-list',
                        'permissions' => ['pensoft.courses.access_topics'],
                    ],
                    'blocks' => [
                        'label'       => 'Blocks',
                        'url'         => Backend::url('pensoft/courses/block'),
                        'icon'        => 'icon-cubes',
                        'permissions' => ['pensoft.courses.access_blocks'],
                    ],
                    'lessons' => [
                        'label'       => 'Lessons',
                        'url'         => Backend::url('pensoft/courses/lesson'),
                        'icon'        => 'icon-book',
                        'permissions' => ['pensoft.courses.access_lessons'],
                    ]
                ]
            ],
        ];
    }
}
