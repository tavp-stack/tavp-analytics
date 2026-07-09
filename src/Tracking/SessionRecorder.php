<?php

declare(strict_types=1);

namespace Tavp\Analytics\Tracking;

use Tavp\Analytics\Models\SessionRecording;

/**
 * Records user interactions (clicks, scrolls, inputs) for session replay.
 * Works on web, mobile, and desktop platforms.
 */
class SessionRecorder
{
    private array $config;

    public function __construct(array $config = [])
    {
        $this->config = array_merge([
            'enabled' => false,
            'max_events' => 10000,
            'record_inputs' => false,
            'record_scroll' => true,
            'record_clicks' => true,
            'record_mouse' => false,
            'record_console' => false,
        ], $config);
    }

    /**
     * Save a session recording with interaction events.
     */
    public function save(array $data): ?SessionRecording
    {
        if (!$this->config['enabled']) {
            return null;
        }

        $events = $data['events'] ?? [];

        // Limit events
        if (count($events) > $this->config['max_events']) {
            $events = array_slice($events, 0, $this->config['max_events']);
        }

        // Filter events based on config
        $events = array_filter($events, function ($event) {
            $type = $event['type'] ?? '';

            return match ($type) {
                'click' => $this->config['record_clicks'],
                'scroll' => $this->config['record_scroll'],
                'input' => $this->config['record_inputs'],
                'mousemove' => $this->config['record_mouse'],
                'console' => $this->config['record_console'],
                default => true,
            };
        });

        $recording = new SessionRecording();
        $recording->fill([
            'session_id' => $data['session_id'],
            'user_id' => $data['user_id'] ?? null,
            'events' => array_values($events),
            'duration' => $data['duration'] ?? 0,
            'viewport_width' => $data['viewport_width'] ?? 1920,
            'viewport_height' => $data['viewport_height'] ?? 1080,
            'started_at' => $data['started_at'] ?? date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $recording->save();

        return $recording;
    }
}
