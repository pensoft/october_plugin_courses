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
    const MAX_FILES = 50;
    const MAX_FILE_SIZE = 10 * 1024 * 1024; // 10MB per file
    const MAX_TOTAL_SIZE = 100 * 1024 * 1024; // 100MB total
    const DOWNLOAD_TIMEOUT = 30; // 30 seconds per file
    const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'];

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
     * Validate if URL is a safe image URL
     */
    private function isValidImageUrl($url)
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