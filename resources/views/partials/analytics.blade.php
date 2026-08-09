@php
    $umamiSrc = \App\Content::option('analytics_umami_src');
    $umamiId = \App\Content::option('analytics_umami_id');
@endphp

@if ($umamiSrc && $umamiId)
  <script defer src="{{ esc_url($umamiSrc) }}" data-website-id="{{ esc_attr($umamiId) }}"></script>
@endif
