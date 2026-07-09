{% extends 'analytics::layouts.app' %}

{% block content %}
<div class="px-4 py-6 sm:px-0" x-data="analyticsDashboard()">
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Analytics Dashboard</h1>
        <p class="mt-1 text-sm text-gray-600">Real-time analytics, fraud detection, and user behavior insights.</p>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
        <div class="bg-white overflow-hidden shadow rounded-lg p-5">
            <dt class="text-sm font-medium text-gray-500 truncate">Pageviews Today</dt>
            <dd class="text-2xl font-bold text-gray-900" x-text="stats.pageviews_today">0</dd>
        </div>
        <div class="bg-white overflow-hidden shadow rounded-lg p-5">
            <dt class="text-sm font-medium text-gray-500 truncate">Unique Visitors</dt>
            <dd class="text-2xl font-bold text-gray-900" x-text="stats.unique_today">0</dd>
        </div>
        <div class="bg-white overflow-hidden shadow rounded-lg p-5">
            <dt class="text-sm font-medium text-gray-500 truncate">Real-time</dt>
            <dd class="text-2xl font-bold text-green-600" x-text="stats.realtime">0</dd>
        </div>
        <div class="bg-white overflow-hidden shadow rounded-lg p-5">
            <dt class="text-sm font-medium text-gray-500 truncate">Fraud Events</dt>
            <dd class="text-2xl font-bold text-red-600" x-text="stats.fraud_events_today">0</dd>
        </div>
    </div>

    <!-- Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Traffic Chart -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Traffic (30 days)</h3>
            <div class="h-64 flex items-end space-x-1" x-show="stats.chart_data.length > 0">
                <template x-for="(value, index) in stats.chart_data" :key="index">
                    <div class="flex-1 bg-indigo-500 rounded-t" :style="'height: ' + getBarHeight(value) + '%'" :title="value + ' pageviews'"></div>
                </template>
            </div>
            <p x-show="stats.chart_data.length === 0" class="text-gray-500 text-center py-8">No data yet</p>
        </div>

        <!-- Device Breakdown -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Devices</h3>
            <div class="space-y-3">
                <template x-for="(count, device) in stats.devices" :key="device">
                    <div class="flex items-center">
                        <span class="w-20 text-sm text-gray-600 capitalize" x-text="device"></span>
                        <div class="flex-1 mx-3 bg-gray-200 rounded-full h-2">
                            <div class="bg-indigo-500 h-2 rounded-full" :style="'width: ' + getPercent(count, getTotalDevices()) + '%'"></div>
                        </div>
                        <span class="text-sm font-medium text-gray-900" x-text="count"></span>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- Top Pages & Countries -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Top Pages</h3>
            <div class="space-y-2">
                <template x-for="page in stats.top_pages.slice(0, 10)" :key="page.path">
                    <div class="flex justify-between items-center py-1">
                        <span class="text-sm text-gray-700 truncate" x-text="page.path"></span>
                        <span class="text-sm font-medium text-gray-900" x-text="page.count"></span>
                    </div>
                </template>
                <p x-show="!stats.top_pages.length" class="text-gray-500 text-center py-4">No data yet</p>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Top Countries</h3>
            <div class="space-y-2">
                <template x-for="country in stats.countries.slice(0, 10)" :key="country.country">
                    <div class="flex justify-between items-center py-1">
                        <span class="text-sm text-gray-700" x-text="country.country || 'Unknown'"></span>
                        <span class="text-sm font-medium text-gray-900" x-text="country.count"></span>
                    </div>
                </template>
                <p x-show="!stats.countries.length" class="text-gray-500 text-center py-4">No data yet</p>
            </div>
        </div>
    </div>

    <!-- Fraud Detection -->
    <div class="bg-white shadow rounded-lg p-6 mb-8">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Fraud Detection Summary</h3>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="text-center">
                <div class="text-2xl font-bold text-red-600" x-text="stats.fraud_events_today">0</div>
                <div class="text-sm text-gray-500">Flagged Today</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-yellow-600" x-text="stats.fraud_score_avg">0.00</div>
                <div class="text-sm text-gray-500">Avg Fraud Score</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-orange-600" x-text="stats.suspicious_sessions">0</div>
                <div class="text-sm text-gray-500">Suspicious Sessions</div>
            </div>
        </div>
    </div>

    <!-- Refresh indicator -->
    <div class="text-center text-sm text-gray-500">
        Auto-refresh in <span x-text="refreshCountdown">30</span>s
    </div>
</div>
{% endblock %}
