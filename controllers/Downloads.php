<?php namespace Pensoft\Courses\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use Response;
use ZipArchive;
use File;
use Storage;
use Illuminate\Http\Request;
use Exception;
use Validator;
use Log;

/**
 * Downloads Back-end Controller
 */
class Downloads extends Controller
{
    // Configuration constants
    const MAX_FILES = 100;
    const MAX_FILE_SIZE = 10 * 1024 * 1024; // 10MB per file
    const MAX_TOTAL_SIZE = 100 * 1024 * 1024; // 100MB total
    const DOWNLOAD_TIMEOUT = 30; // 30 seconds per file
    const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'];

    /**
     * Download block materials as zip
     */
    public function downloadBlock(Request $request)
    {
        try {
            // Get JSON input
            $input = json_decode($request->getContent(), true);
            
            // Validate input
            $validator = Validator::make($input, [
                'block_id' => 'required|string|max:255',
                'block_name' => 'required|string|max:255',
                'materials' => 'required|array|max:' . self::MAX_FILES,
                'materials.*.id' => 'required|string|max:255',
                'materials.*.name' => 'required|string|max:255',
                'materials.*.resources' => 'required|array',
                'materials.*.resources.*.url' => 'required|url|max:2048',
                'materials.*.resources.*.type' => 'required|string|in:cover,video,document,gallery',
                'materials.*.resources.*.name' => 'required|string|max:255'
            ]);

            if ($validator->fails()) {
                return Response::json(['error' => 'Invalid input data'], 400);
            }

            $blockId = $input['block_id'];
            $blockName = $input['block_name'];
            $materials = $input['materials'];

            // Create a temporary directory for this download
            $tempDir = storage_path('temp/block_' . uniqid());
            if (!File::exists($tempDir)) {
                File::makeDirectory($tempDir, 0755, true);
            }

            $downloadedFiles = [];
            $totalSize = 0;
            
            foreach ($materials as $material) {
                $materialDir = $tempDir . '/' . $this->sanitizeFileName($material['name']);
                if (!File::exists($materialDir)) {
                    File::makeDirectory($materialDir, 0755, true);
                }

                foreach ($material['resources'] as $resource) {
                    try {
                        // Validate resource URL
                        if (!$this->isValidResourceUrl($resource['url'], $resource['type'])) {
                            Log::warning("Invalid resource URL, skipping: {$resource['url']}");
                            continue;
                        }

                        $resourceData = $this->downloadResourceSafely($resource['url']);
                        if ($resourceData !== false) {
                            // Check file size
                            if (strlen($resourceData) > self::MAX_FILE_SIZE) {
                                Log::warning("Resource too large, skipping: {$resource['url']}");
                                continue;
                            }

                            $totalSize += strlen($resourceData);
                            if ($totalSize > self::MAX_TOTAL_SIZE) {
                                Log::warning("Total size limit exceeded, stopping download");
                                break 2; // Break both loops
                            }

                            // Get file extension and create filename
                            $extension = $this->getResourceExtension($resource['url'], $resource['type']);
                            $fileName = $this->sanitizeFileName($resource['name']) . '.' . $extension;
                            $filePath = $materialDir . '/' . $fileName;
                            
                            file_put_contents($filePath, $resourceData);
                            $downloadedFiles[] = $filePath;
                        }
                    } catch (Exception $e) {
                        Log::warning("Failed to download resource: {$resource['url']}. Error: " . $e->getMessage());
                        continue;
                    }
                }
            }

            if (empty($downloadedFiles)) {
                $this->cleanupTempDir($tempDir);
                return Response::json(['error' => 'No resources could be downloaded'], 400);
            }

            // Create zip file
            $zipFileName = $this->sanitizeFileName($blockName) . '_materials.zip';
            $zipPath = $tempDir . '/' . $zipFileName;
            
            $zip = new ZipArchive();
            if ($zip->open($zipPath, ZipArchive::CREATE) !== TRUE) {
                $this->cleanupTempDir($tempDir);
                return Response::json(['error' => 'Could not create zip file'], 500);
            }

            // Add files to zip with proper folder structure
            foreach ($downloadedFiles as $filePath) {
                $relativePath = str_replace($tempDir . '/', '', $filePath);
                $zip->addFile($filePath, $relativePath);
            }
            $zip->close();

            // Serve the zip file
            $response = Response::download($zipPath, $zipFileName)->deleteFileAfterSend(true);
            
            // Clean up temp directory after a delay
            register_shutdown_function(function() use ($tempDir) {
                $this->cleanupTempDir($tempDir);
            });

            return $response;

        } catch (Exception $e) {
            Log::error('Block download failed: ' . $e->getMessage());
            return Response::json(['error' => 'Download failed'], 500);
        }
    }

    /**
     * Download gallery as zip
     */
    public function downloadGallery(Request $request)
    {
        try {
            // Validate input
            $validator = Validator::make($request->all(), [
                'material_id' => 'required|string|max:255',
                'material_name' => 'required|string|max:255',
                'gallery_urls' => 'required|array|max:' . self::MAX_FILES,
                'gallery_urls.*' => 'required|url|max:2048'
            ]);

            if ($validator->fails()) {
                return Response::json(['error' => 'Invalid input data'], 400);
            }

            $materialId = $request->input('material_id');
            $materialName = $request->input('material_name');
            $galleryUrls = $request->input('gallery_urls');

            // Additional security validation
            foreach ($galleryUrls as $url) {
                if (!$this->isValidImageUrl($url)) {
                    return Response::json(['error' => 'Invalid image URL detected'], 400);
                }
            }

            // Create a temporary directory for this download
            $tempDir = storage_path('temp/gallery_' . uniqid());
            if (!File::exists($tempDir)) {
                File::makeDirectory($tempDir, 0755, true);
            }

            // Download images to temp directory
            $downloadedFiles = [];
            $totalSize = 0;
            
            foreach ($galleryUrls as $index => $imageUrl) {
                try {
                    $imageData = $this->downloadImageSafely($imageUrl);
                    if ($imageData !== false) {
                        // Check file size
                        if (strlen($imageData) > self::MAX_FILE_SIZE) {
                            Log::warning("Image too large, skipping: {$imageUrl}");
                            continue;
                        }

                        $totalSize += strlen($imageData);
                        if ($totalSize > self::MAX_TOTAL_SIZE) {
                            Log::warning("Total size limit exceeded, stopping download");
                            break;
                        }

                        // Get file extension from URL
                        $extension = $this->getValidExtension($imageUrl);
                        $fileName = sprintf('image_%03d.%s', $index + 1, $extension);
                        $filePath = $tempDir . '/' . $fileName;
                        
                        file_put_contents($filePath, $imageData);
                        $downloadedFiles[] = $filePath;
                    }
                } catch (Exception $e) {
                    Log::warning("Failed to download image: {$imageUrl}. Error: " . $e->getMessage());
                    continue;
                }
            }

            if (empty($downloadedFiles)) {
                $this->cleanupTempDir($tempDir);
                return Response::json(['error' => 'No images could be downloaded'], 400);
            }

            // Create zip file
            $zipFileName = $this->sanitizeFileName($materialName) . '_gallery.zip';
            $zipPath = $tempDir . '/' . $zipFileName;
            
            $zip = new ZipArchive();
            if ($zip->open($zipPath, ZipArchive::CREATE) !== TRUE) {
                $this->cleanupTempDir($tempDir);
                return Response::json(['error' => 'Could not create zip file'], 500);
            }

            // Add files to zip
            foreach ($downloadedFiles as $filePath) {
                $zip->addFile($filePath, basename($filePath));
            }
            $zip->close();

            // Serve the zip file
            $response = Response::download($zipPath, $zipFileName)->deleteFileAfterSend(true);
            
            // Clean up temp directory after a delay
            register_shutdown_function(function() use ($tempDir) {
                $this->cleanupTempDir($tempDir);
            });

            return $response;

        } catch (Exception $e) {
            Log::error('Gallery download failed: ' . $e->getMessage());
            return Response::json(['error' => 'Download failed'], 500);
        }
    }

    /**
     * Clean up temporary directory
     */
    private function cleanupTempDir($tempDir)
    {
        try {
            if (File::exists($tempDir)) {
                File::deleteDirectory($tempDir);
            }
        } catch (Exception $e) {
            // Log error but don't fail the download
            \Log::error('Failed to cleanup temp directory: ' . $e->getMessage());
        }
    }

    /**
     * Download image safely with timeout and size limits
     */
    private function downloadImageSafely($url)
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => self::DOWNLOAD_TIMEOUT,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; CourseDownloader/1.0)',
            CURLOPT_MAXFILESIZE => self::MAX_FILE_SIZE,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS
        ]);
        
        $data = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($data === false || $httpCode !== 200) {
            return false;
        }
        
        return $data;
    }

    /**
     * Validate if URL is a safe resource URL
     */
    private function isValidResourceUrl($url, $type)
    {
        // Parse URL
        $parsed = parse_url($url);
        if (!$parsed || !isset($parsed['scheme']) || !isset($parsed['host'])) {
            return false;
        }

        // Only allow HTTP/HTTPS
        if (!in_array($parsed['scheme'], ['http', 'https'])) {
            return false;
        }

        // Prevent access to localhost/private IPs (basic check)
        $host = $parsed['host'];
        if (in_array($host, ['localhost', '127.0.0.1', '::1']) || 
            strpos($host, '192.168.') === 0 || 
            strpos($host, '10.') === 0) {
            return false;
        }

        // URL looks valid - let cURL handle the rest
        return true;
    }

    /**
     * Validate if URL is a safe image URL (legacy method for backward compatibility)
     */
    private function isValidImageUrl($url)
    {
        return $this->isValidResourceUrl($url, 'image');
    }

    /**
     * Download resource safely with timeout and size limits
     */
    private function downloadResourceSafely($url)
    {
        return $this->downloadImageSafely($url); // Reuse existing method
    }



    /**
     * Get valid file extension from URL
     */
    private function getValidExtension($url)
    {
        $path = parse_url($url, PHP_URL_PATH);
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        
        // If no extension or invalid extension, try to detect from content-type later
        return in_array($extension, self::ALLOWED_EXTENSIONS) ? $extension : 'jpg';
    }

    /**
     * Get appropriate file extension based on resource type and URL
     */
    private function getResourceExtension($url, $type)
    {
        $path = parse_url($url, PHP_URL_PATH);
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        
        // If we have a valid extension, use it
        if ($extension) {
            return $extension;
        }
        
        // Otherwise, provide defaults based on resource type
        switch ($type) {
            case 'cover':
            case 'gallery':
                return 'jpg';
            case 'video':
                return 'mp4';
            case 'document':
                return 'pdf';
            default:
                return 'bin'; // generic binary file
        }
    }

    /**
     * Sanitize filename for download
     */
    private function sanitizeFileName($filename)
    {
        // Remove special characters and spaces
        $sanitized = preg_replace('/[^a-zA-Z0-9_-]/', '_', $filename);
        // Remove multiple underscores
        $sanitized = preg_replace('/_+/', '_', $sanitized);
        // Trim underscores from start and end
        return trim($sanitized, '_') ?: 'gallery';
    }
} 