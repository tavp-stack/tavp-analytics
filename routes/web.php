<?php

declare(strict_types=1);

use Tavp\Analytics\Http\Controllers\TrackingController;
use Tavp\Core\Routing\Router;

/** @var Router $router */

$router->group([
    'prefix' => '/analytics',
], function (Router $router) {

    $router->get('/', function () {
        $stats = new \Tavp\Analytics\Analytics\StatsEngine();

        return view('analytics::dashboard.index', [
            'title' => 'Analytics Dashboard',
            'stats' => $stats->getStats(),
        ]);
    })->name('analytics.dashboard');
});
