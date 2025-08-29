@php
    $logoPath = \App\Models\Setting::getLogoPath();
    $appName = \App\Models\Setting::getAppName();
@endphp

@if($logoPath && Storage::disk('public')->exists($logoPath))
    <img src="{{ Storage::url($logoPath) }}" 
         alt="{{ $appName }}" 
         {{ $attributes->merge(['class' => 'object-contain']) }}>
@else
    <div {{ $attributes->merge(['class' => 'font-bold text-xl']) }}>
        @if($appName === 'EQOS WorkTime')
            <span class="text-blue-600">EQOS</span> 
            <span class="text-gray-800 dark:text-gray-200">WorkTime</span>
        @else
            <span class="text-blue-600">{{ $appName }}</span>
        @endif
    </div>
@endif
