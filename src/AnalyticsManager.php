<?php

declare(strict_types=1);

namespace Tavp\Analytics;

use Tavp\Analytics\Http\Controllers\TrackingController;
use Tavp\Core\Routing\Router;

/**
 * TAVP Analytics service provider.
 * Registers routes, middleware, and services.
 */
class AnalyticsManager
{
    public static function register(Router $router, array $config = []): void
    {
        $config = array_merge(require __DIR__ . '/../config/analytics.php', $config);

        if (!$config['enabled']) {
            return;
        }

        // Register API routes
        require_once __DIR__ . '/../routes/api.php';

        // Register dashboard routes if enabled
        if ($config['dashboard_enabled']) {
            require_once __DIR__ . '/../routes/web.php';
        }
    }

    /**
     * Get the JavaScript tracker snippet for embedding.
     */
    public static function trackerScript(array $config = []): string
    {
        $endpoint = $config['endpoint'] ?? '/api/analytics';
        $sessionRecording = ($config['session_recording'] ?? false) ? 'true' : 'false';

        return <<<HTML
<script>
window.tavpAnalyticsConfig = {
    endpoint: '{$endpoint}',
    sessionRecording: {$sessionRecording}
};
</script>
<script src="{$endpoint}/../js/tracker.js" defer></script>
HTML;
    }
}
