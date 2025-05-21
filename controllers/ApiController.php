<?php namespace Pensoft\Courses\Controllers;

use Backend\Classes\Controller;
use Illuminate\Http\Request;
use Pensoft\Courses\Models\Material;
use Illuminate\Support\Facades\Validator;
use System\Classes\PluginManager;
use Response;

/**
 * API Controller
 */
class ApiController extends Controller
{
    public $implement = [];

    /**
     * API endpoint for filtering materials
     * GET /api/pensoft/courses/materials
     * 
     * @param Request $request
     * @return Response
     */
    public function getMaterials(Request $request)
    {
        // Get filter parameters
        $language = $request->input('language');
        $level = $request->input('level');
        $department = $request->input('department');
        $type = $request->input('type');
        $search = $request->input('search');
        
        // Start query with necessary relations
        $query = Material::with(['lesson', 'lesson.block', 'lesson.block.topic']);
        
        // Apply filters
        if ($language) {
            $query->where('language', $language);
        }
        
        if ($level) {
            $query->whereHas('lesson.block', function($q) use ($level) {
                $q->where('level', $level);
            });
        }
        
        if ($department) {
            $query->whereHas('lesson', function($q) use ($department) {
                $q->where('department', $department);
            });
        }
        
        if ($type) {
            $query->where('type', $type);
        }
        
        // Apply search
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        // Paginate results
        $perPage = $request->input('per_page', 15);
        $materials = $query->paginate($perPage);
        
        return Response::json($materials);
    }
} 