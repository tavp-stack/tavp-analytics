<?php

declare(strict_types=1);

// Analytics API routes.

use Tavp\Analytics\Http\Controllers\ExperimentController;
use Tavp\Analytics\Http\Controllers\TrackingController;
use Tavp\Core\Http\Middleware\ThrottleRequests;
use Tavp\Core\Routing\Router;

/** @var Router $router */

$router->group([
    'prefix' => '/api/analytics',
    'middleware' => [ThrottleRequests::class],
], function (Router $router) {

    // Tracking endpoints
    $router->post('/track', [TrackingController::class, 'track'])->name('analytics.track');
    $router->post('/event', [TrackingController::class, 'event'])->name('analytics.event');
    $router->post('/session', [TrackingController::class, 'session'])->name('analytics.session');
    $router->post('/verify', [TrackingController::class, 'verify'])->name('analytics.verify');
    $router->get('/stats', [TrackingController::class, 'stats'])->name('analytics.stats');

    // Experiment endpoints
    $router->get('/experiment/{slug}/variant', [ExperimentController::class, 'variant'])->name('analytics.experiment.variant');
    $router->post('/experiment/{slug}/convert', [ExperimentController::class, 'convert'])->name('analytics.experiment.convert');
    $router->get('/experiment/{slug}/results', [ExperimentController::class, 'results'])->name('analytics.experiment.results');

    // Funnel endpoints
    $router->post('/funnel/{slug}/step', [ExperimentController::class, 'funnelStep'])->name('analytics.funnel.step');
    $router->get('/funnel/{slug}/results', [ExperimentController::class, 'funnelResults'])->name('analytics.funnel.results');
});
