@props([
    'title' => 'SELECT AN ITEM',
    'subtitle' => 'Current Ward Stock Balance',
    'metricValue' => '0 Units',
    'metricLabel' => 'AVAILABLE',
    'metricColor' => '#16a34a'
])

<div class="gradient-header-card">
    <div>
        <h1 class="header-card-title mb-1">{{ $title }}</h1>
        <div class="header-card-subtitle">{{ $subtitle }}</div>
    </div>
    
    <div class="header-metric-box">
        <div class="header-metric-value" style="color: {{ $metricColor }};">
            {{ $metricValue }}
        </div>
        <div class="header-metric-label" style="color: {{ $metricColor }};">
            {{ $metricLabel }}
        </div>
    </div>
</div>
