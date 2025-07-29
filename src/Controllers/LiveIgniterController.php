<?php

namespace LiveIgniter\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\ResponseInterface;
use LiveIgniter\ComponentManager;

/**
 * LiveIgniterController
 * 
 * Handles AJAX requests for LiveIgniter components
 */
class LiveIgniterController extends Controller
{
    /**
     * Component manager instance
     */
    protected ComponentManager $manager;
    
    public function __construct()
    {
        parent::__construct();
        $this->manager = new ComponentManager();
    }
    
    /**
     * Handle component method calls
     */
    public function call(): ResponseInterface
    {
        // Ensure this is an AJAX request
        if (!$this->request->isAJAX()) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON(['error' => 'This endpoint only accepts AJAX requests']);
        }
        
        return $this->manager->handleAjaxCall();
    }
    
    /**
     * Serve LiveIgniter JavaScript file
     */
    public function serveJs(): ResponseInterface
    {
        $jsPath = __DIR__ . '/../../public/liveigniter.js';
        
        if (!file_exists($jsPath)) {
            return $this->response->setStatusCode(404);
        }
        
        $jsContent = file_get_contents($jsPath);
        
        return $this->response
            ->setContentType('application/javascript')
            ->setBody($jsContent);
    }
    
    /**
     * Serve LiveIgniter assets
     */
    public function serveAsset(string $asset): ResponseInterface
    {
        $assetsPath = __DIR__ . '/../../public/';
        $assetPath = $assetsPath . $asset;
        
        // Security check - prevent directory traversal
        if (strpos(realpath($assetPath), realpath($assetsPath)) !== 0) {
            return $this->response->setStatusCode(403);
        }
        
        if (!file_exists($assetPath)) {
            return $this->response->setStatusCode(404);
        }
        
        $content = file_get_contents($assetPath);
        $mimeType = $this->getMimeType($assetPath);
        
        return $this->response
            ->setContentType($mimeType)
            ->setBody($content);
    }
    
    /**
     * Debug components (development only)
     */
    public function debugComponents(): ResponseInterface
    {
        if (ENVIRONMENT !== 'development') {
            return $this->response->setStatusCode(404);
        }
        
        $session = session();
        $components = [];
        
        // Get all LiveIgniter component sessions
        $sessionData = $session->get();
        foreach ($sessionData as $key => $value) {
            if (strpos($key, 'liveigniter.components.') === 0) {
                $componentId = substr($key, strlen('liveigniter.components.'));
                $components[$componentId] = $value;
            }
        }
        
        return $this->response->setJSON([
            'components' => $components,
            'count' => count($components)
        ]);
    }
    
    /**
     * Debug sessions (development only)
     */
    public function debugSessions(): ResponseInterface
    {
        if (ENVIRONMENT !== 'development') {
            return $this->response->setStatusCode(404);
        }
        
        $session = session();
        $sessionData = $session->get();
        
        $liveigniterData = [];
        foreach ($sessionData as $key => $value) {
            if (strpos($key, 'liveigniter.') === 0) {
                $liveigniterData[$key] = $value;
            }
        }
        
        return $this->response->setJSON([
            'session_id' => $session->session_id,
            'liveigniter_data' => $liveigniterData,
            'total_session_data' => count($sessionData)
        ]);
    }
    
    /**
     * Get MIME type for file
     */
    protected function getMimeType(string $filePath): string
    {
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        
        $mimeTypes = [
            'js' => 'application/javascript',
            'css' => 'text/css',
            'json' => 'application/json',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'ico' => 'image/x-icon',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf' => 'font/ttf',
            'eot' => 'application/vnd.ms-fontobject'
        ];
        
        return $mimeTypes[$extension] ?? 'application/octet-stream';
    }
}
