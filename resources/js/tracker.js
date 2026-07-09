/**
 * TAVP Analytics — Advanced tracking script.
 * Works on web, mobile web, and hybrid apps.
 *
 * Features:
 * - Page view tracking
 * - Custom event tracking
 * - Session management
 * - Duration & bounce detection
 * - SPA support (history API)
 * - Session recording (clicks, scrolls)
 * - A/B test variant assignment
 * - Fraud signal collection
 * - Device fingerprinting
 */
(function() {
    'use strict';

    var config = {
        endpoint: '/api/analytics',
        sessionDuration: 30,
        sessionRecording: false,
        recordClicks: true,
        recordScrolls: true,
        recordInputs: false,
        maxRecordingEvents: 10000
    };

    // Merge config from global
    if (window.tavpAnalyticsConfig) {
        for (var key in window.tavpAnalyticsConfig) {
            config[key] = window.tavpAnalyticsConfig[key];
        }
    }

    // Session management
    var sessionId = getOrCreateSessionId();
    var pageLoadTime = Date.now();
    var eventCount = 0;
    var recordingEvents = [];

    function getOrCreateSessionId() {
        var stored = sessionStorage.getItem('tavp_session');
        if (stored) return stored;
        var id = 'sess_' + Math.random().toString(36).substring(2, 14) + '_' + Date.now();
        sessionStorage.setItem('tavp_session', id);
        return id;
    }

    function sendBeacon(url, data) {
        if (navigator.sendBeacon) {
            var blob = new Blob([JSON.stringify(data)], { type: 'application/json' });
            navigator.sendBeacon(url, blob);
        } else {
            fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data),
                keepalive: true
            }).catch(function() {});
        }
    }

    // Page view tracking
    function trackPageView() {
        var data = {
            path: window.location.pathname,
            title: document.title,
            referrer: document.referrer,
            session_id: sessionId,
            duration: 0,
            is_bounce: false,
            platform: detectPlatform(),
            screen_resolution: window.screen.width + 'x' + window.screen.height,
            timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
            event_count: eventCount
        };

        sendBeacon(config.endpoint + '/track', data);
    }

    function detectPlatform() {
        var ua = navigator.userAgent;
        if (/android/i.test(ua)) return 'android';
        if (/iphone|ipad|ipod/i.test(ua)) return 'ios';
        if (window.tavpPlatform) return window.tavpPlatform;
        return 'web';
    }

    // Duration & bounce tracking
    function trackDuration() {
        var duration = Math.floor((Date.now() - pageLoadTime) / 1000);

        var data = {
            path: window.location.pathname,
            session_id: sessionId,
            duration: duration,
            is_bounce: duration < 30 && eventCount <= 1,
            page_views_in_session: parseInt(sessionStorage.getItem('tavp_pageviews') || '0')
        };

        sendBeacon(config.endpoint + '/track', data);
    }

    // Update page view count in session
    var pageViews = parseInt(sessionStorage.getItem('tavp_pageviews') || '0');
    pageViews++;
    sessionStorage.setItem('tavp_pageviews', pageViews.toString());

    // Custom event tracking
    window.tavpAnalytics = {
        event: function(name, category, label, value, metadata) {
            eventCount++;
            var data = {
                event_name: name,
                event_category: category || null,
                event_label: label || null,
                event_value: value || null,
                path: window.location.pathname,
                session_id: sessionId,
                platform: detectPlatform(),
                metadata: metadata || null
            };

            sendBeacon(config.endpoint + '/event', data);
        },

        captcha: function(context, success) {
            this.event('captcha_' + (success ? 'success' : 'fail'), 'security', context);
        },

        convert: function(experimentSlug) {
            sendBeacon(config.endpoint + '/experiment/' + experimentSlug + '/convert', {
                session_id: sessionId
            });
        },

        funnel: function(funnelSlug, stepIndex, metadata) {
            sendBeacon(config.endpoint + '/funnel/' + funnelSlug + '/step', {
                step: stepIndex,
                metadata: metadata || null
            });
        },

        getVariant: function(experimentSlug, callback) {
            fetch(config.endpoint + '/experiment/' + experimentSlug + '/variant')
                .then(function(r) { return r.json(); })
                .then(function(data) { callback(data.variant); })
                .catch(function() { callback(null); });
        }
    };

    // Session recording
    if (config.sessionRecording) {
        if (config.recordClicks) {
            document.addEventListener('click', function(e) {
                if (recordingEvents.length >= config.maxRecordingEvents) return;
                recordingEvents.push({
                    type: 'click',
                    x: e.clientX,
                    y: e.clientY,
                    target: e.target.tagName,
                    timestamp: Date.now() - pageLoadTime
                });
            }, true);
        }

        if (config.recordScrolls) {
            var lastScrollY = 0;
            window.addEventListener('scroll', function() {
                if (recordingEvents.length >= config.maxRecordingEvents) return;
                var scrollY = window.scrollY;
                if (Math.abs(scrollY - lastScrollY) > 50) {
                    recordingEvents.push({
                        type: 'scroll',
                        y: scrollY,
                        timestamp: Date.now() - pageLoadTime
                    });
                    lastScrollY = scrollY;
                }
            }, { passive: true });
        }

        // Save recording on page unload
        window.addEventListener('beforeunload', function() {
            if (recordingEvents.length === 0) return;

            var data = {
                session_id: sessionId,
                events: recordingEvents,
                duration: Math.floor((Date.now() - pageLoadTime) / 1000),
                viewport_width: window.innerWidth,
                viewport_height: window.innerHeight,
                started_at: new Date(pageLoadTime).toISOString()
            };

            sendBeacon(config.endpoint + '/session', data);
        });
    }

    // SPA support
    if (window.history) {
        var originalPushState = history.pushState;
        history.pushState = function() {
            trackDuration();
            originalPushState.apply(this, arguments);
            pageLoadTime = Date.now();
            eventCount = 0;
            trackPageView();
        };

        window.addEventListener('popstate', function() {
            trackDuration();
            pageLoadTime = Date.now();
            eventCount = 0;
            trackPageView();
        });
    }

    // Track page view on load
    trackPageView();

    // Track duration on unload
    window.addEventListener('beforeunload', trackDuration);
    document.addEventListener('visibilitychange', function() {
        if (document.visibilityState === 'hidden') {
            trackDuration();
        }
    });

})();
