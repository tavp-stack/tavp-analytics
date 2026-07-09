<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ title ?? 'Analytics' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-50 min-h-screen">
    <nav class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <span class="text-xl font-bold text-indigo-600">TAVP Analytics</span>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        {% block content %}{% endblock %}
    </main>

    <script>
    function analyticsDashboard() {
        return {
            stats: {
                pageviews_today: 0,
                pageviews_month: 0,
                unique_today: 0,
                unique_month: 0,
                bounce_rate: 0,
                avg_duration: '0:00',
                realtime: 0,
                top_pages: [],
                referrers: [],
                countries: [],
                devices: { desktop: 0, mobile: 0, tablet: 0 },
                browsers: {},
                os: {},
                platforms: {},
                total_events: 0,
                fraud_events_today: 0,
                fraud_score_avg: 0,
                suspicious_sessions: 0,
                chart_data: []
            },
            refreshCountdown: 30,

            init() {
                this.fetchStats();
                setInterval(() => {
                    this.refreshCountdown--;
                    if (this.refreshCountdown <= 0) {
                        this.fetchStats();
                        this.refreshCountdown = 30;
                    }
                }, 1000);
            },

            fetchStats() {
                fetch('/api/analytics/stats')
                    .then(r => r.json())
                    .then(data => {
                        if (data.stats) this.stats = data.stats;
                    })
                    .catch(() => {});
            },

            getBarHeight(value) {
                var max = Math.max(...this.stats.chart_data, 1);
                return (value / max) * 100;
            },

            getTotalDevices() {
                var d = this.stats.devices;
                return (d.desktop || 0) + (d.mobile || 0) + (d.tablet || 0) || 1;
            },

            getPercent(value, total) {
                return total > 0 ? (value / total) * 100 : 0;
            }
        };
    }
    </script>
</body>
</html>
