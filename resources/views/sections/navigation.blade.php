@php
    // The React header split the site name across two spans, stacking them on
    // mobile. Splitting on the first space keeps that behaviour driven by the
    // WordPress site title.
    $brand = preg_split('/\s+/', get_bloginfo('name', 'display'), 2);
@endphp

<nav
  data-nav
  class="fixed top-0 left-0 right-0 z-50 transition-all duration-300 pt-4 pb-4 sm:pt-0 bg-transparent"
>
  <div class="container mx-auto px-4 sm:px-6 lg:px-8 relative z-50">
    <div class="flex items-center justify-between h-16 sm:h-20">
      <a href="{{ home_url('/') }}" class="font-fira text-lg sm:text-xl font-bold hover:text-accent transition-colors">
        <span class="text-foreground">{{ $brand[0] ?? '' }}</span>
        @if (! empty($brand[1]))
          <br class="sm:hidden">
          <span class="text-foreground sm:ml-2">{{ $brand[1] }}</span>
        @endif
      </a>

      {{-- Desktop navigation --}}
      <div class="hidden md:flex items-center space-x-8">
        @foreach ($navLinks as $link)
          <a
            href="{{ esc_url($link['href']) }}"
            class="font-fira text-sm text-muted-foreground hover:text-foreground transition-colors"
          >
            {{ $link['label'] }}
          </a>
        @endforeach
      </div>

      {{-- Mobile menu button --}}
      <div class="flex items-center gap-4 md:hidden">
        <button
          type="button"
          data-nav-toggle
          aria-expanded="false"
          aria-controls="mobile-menu"
          aria-label="{{ __('Toggle menu', 'edgar-ferreira') }}"
          class="inline-flex h-10 w-10 items-center justify-center rounded-md text-foreground hover:bg-secondary/70 transition-colors"
        >
          <x-icon name="menu" data-nav-icon-open class="h-5 w-5" />
          <x-icon name="x" data-nav-icon-close class="h-5 w-5 hidden" />
        </button>
      </div>
    </div>
  </div>

  {{-- Mobile menu --}}
  <div
    id="mobile-menu"
    data-nav-menu
    class="fixed inset-0 z-40 bg-background/95 backdrop-blur-sm transition-all duration-300 ease-in-out flex flex-col items-center justify-center md:hidden opacity-0 pointer-events-none"
  >
    <div class="container mx-auto px-4 py-4 space-y-8 flex flex-col items-center text-center">
      @foreach ($navLinks as $link)
        <a
          href="{{ esc_url($link['href']) }}"
          data-nav-link
          class="block font-fira text-2xl font-medium text-foreground hover:text-accent transition-colors"
        >
          {{ $link['label'] }}
        </a>
      @endforeach
    </div>
  </div>

  {{-- Scroll progress bar --}}
  <div
    data-nav-progress
    class="absolute bottom-0 left-0 h-[2px] bg-gradient-to-r from-accent to-primary transition-all duration-100 ease-out"
    style="width: 0%"
  ></div>
</nav>
