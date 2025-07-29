<?php

/**
 * LiveIgniter Routes
 * 
 * This file defines routes for LiveIgniter component method calls
 */

use CodeIgniter\Router\RouteCollection;
use LiveIgniter\Controllers\LiveIgniterController;

/**
 * @var RouteCollection $routes
 */

// LiveIgniter AJAX endpoint for component method calls
$routes->post('liveigniter/call', [LiveIgniterController::class, 'call']);

// LiveIgniter asset routes (if serving assets through CodeIgniter)
$routes->get('liveigniter/assets/js/liveigniter.js', [LiveIgniterController::class, 'serveJs']);
$routes->get('liveigniter/assets/(:any)', [LiveIgniterController::class, 'serveAsset/$1']);

// Development routes (remove in production)
if (ENVIRONMENT === 'development') {
    $routes->get('liveigniter/debug/components', [LiveIgniterController::class, 'debugComponents']);
    $routes->get('liveigniter/debug/sessions', [LiveIgniterController::class, 'debugSessions']);
}
