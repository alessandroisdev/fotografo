@php
    $hex = config('settings.primary_color', '#0d6efd');
    $hexClean = ltrim($hex, '#');
    if (strlen($hexClean) == 3) {
        $hexClean = str_repeat(substr($hexClean,0,1), 2) . str_repeat(substr($hexClean,1,1), 2) . str_repeat(substr($hexClean,2,1), 2);
    }
    $r = hexdec(substr($hexClean,0,2));
    $g = hexdec(substr($hexClean,2,2));
    $b = hexdec(substr($hexClean,4,2));
    $rgb = "$r, $g, $b";
@endphp
<style>
    :root, [data-bs-theme="light"], body {
        --bs-primary: {{ $hex }};
        --bs-primary-rgb: {{ $rgb }};
    }
    /* Fallbacks force in case CSS compilation is strict */
    .bg-primary { background-color: var(--bs-primary) !important; }
    .text-primary { color: var(--bs-primary) !important; }
    .btn-primary { 
        background-color: var(--bs-primary); 
        border-color: var(--bs-primary);
        --bs-btn-hover-bg: rgba({{ $rgb }}, 0.85);
        --bs-btn-hover-border-color: rgba({{ $rgb }}, 0.85);
    }
</style>
