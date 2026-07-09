# TAVP Analytics

Advanced analytics, fraud detection, and user behavior tracking for the TAVP stack.

**Version: 1.0.0**

## Requirements

- PHP 8.3+
- Phalcon 5.16+
- tavp-core

## Features

### Core Analytics
- **Page View Tracking** — every page view across web, mobile, and desktop
- **Session Tracking** — full session lifecycle with duration, bounce detection
- **Custom Events** — track any user action with metadata
- **Real-time Dashboard** — live stats with auto-refresh
- **Geographic Analytics** — country, city, region, ISP
- **Device Analytics** — device type, browser, OS, screen resolution
- **Platform Analytics** — web, iOS, Android, desktop app, mobile app

### Fraud Detection
- **Bot Detection** — known bots, crawlers, headless browsers
- **Velocity Checks** — too many requests in short time windows
- **Anomaly Detection** — statistical outlier detection
- **Pattern Detection** — SQL injection, path traversal, known attack patterns
- **Click Fraud** — rapid inhuman click detection
- **Device Fingerprint** — suspicious device characteristics
- **Geographic Rules** — VPN, datacenter IPs, impossible travel

### Fraudless Verification
- **Data Authenticity Scoring** — confirm data represents real human behavior
- **Behavioral Signals** — mouse movement, scroll, focus events, timing patterns
- **Batch Verification** — verify multiple events at once
- **Confidence Scoring** — high/medium/low confidence ratings

### Advanced Features
- **A/B Testing** — experiment tracking with variant assignment and conversion
- **Funnel Analysis** — multi-step conversion funnels
- **Session Recording** — click and scroll event replay
- **SPA Support** — automatic tracking for single-page applications

## Installation

```bash
# Add to your TAVP project
composer require tavp/analytics
```

## Quick Start

### 1. Run Migrations

```bash
tavp migrate
```

### 2. Add Tracker to Your Layout

```php
<script src="/js/tracker.js" defer></script>
```

### 3. Track Custom Events

```javascript
// From JavaScript
window.tavpAnalytics.event('button_click', 'engagement', 'signup_button');

// From PHP
tavp_analytics_event('button_click', 'engagement', 'signup_button');
```

## Configuration

Edit `config/analytics.php`:

```php
return [
    'enabled' => true,
    'track_page_views' => true,
    'fraud_detection_enabled' => true,
    'session_recording_enabled' => false,
    'fraud' => [
        'bot_detection' => true,
        'velocity_limit' => 100,
        'block_suspicious' => false,
    ],
];
```

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/analytics/track` | Track a page view |
| POST | `/api/analytics/event` | Track a custom event |
| POST | `/api/analytics/session` | Save session recording |
| POST | `/api/analytics/verify` | Verify data authenticity |
| GET | `/api/analytics/stats` | Get analytics statistics |
| GET | `/api/analytics/experiment/{slug}/variant` | Get A/B test variant |
| POST | `/api/analytics/experiment/{slug}/convert` | Record conversion |
| POST | `/api/analytics/funnel/{slug}/step` | Record funnel step |

## Dashboard

Access the analytics dashboard at `/analytics`.

## Fraud Detection

The fraud detector runs 7 rules:

1. **Bot Detection** — flags known bots and headless browsers
2. **Velocity** — blocks rapid-fire requests
3. **Anomaly Detection** — statistical outliers in behavior
4. **Device Fingerprint** — mismatched device characteristics
5. **Geographic** — VPN, datacenter IPs
6. **Pattern Detection** — injection attacks, path traversal
7. **Click Fraud** — inhuman click patterns

Each rule returns a score 0.0-1.0. The composite score determines action:
- < 0.5: Clean
- 0.5-0.8: Flagged for review
- > 0.8: Blocked (if enabled)

## License

MIT
