@php
    $total = count($projects);

    /**
     * Initial carousel geometry for activeIndex = 0, matching the transform
     * math in the React component so the first paint needs no JS.
     */
    $slideStyle = function (int $index) use ($total) {
        $offset = $index;
        $isActive = $offset === 0;
        $isPrev = $offset === -1 || $offset === $total - 1;
        $isNext = $offset === 1 || $offset === -$total + 1;

        if (! ($isActive || $isPrev || $isNext)) {
            return 'display: none;';
        }

        $translate = $offset * 100;
        if ($offset < -1) {
            $translate = -200;
        }
        if ($offset > 1) {
            $translate = 200;
        }

        return sprintf(
            'transform: translateX(%d%%) scale(%s); opacity: %s; z-index: %d; pointer-events: %s;',
            $translate,
            $isActive ? '1' : '0.85',
            $isActive ? '1' : '0.3',
            $isActive ? 30 : (($isPrev || $isNext) ? 20 : 10),
            $isActive ? 'auto' : 'none'
        );
    };
@endphp

<section
  class="reveal-section min-h-screen flex items-center pt-32 pb-16 px-4 relative overflow-x-hidden"
  style="transition-delay: 80ms"
>
  {{-- Background accents --}}
  <div class="absolute inset-y-0 left-1/2 w-[640px] h-[640px] -translate-x-1/2 bg-gradient-to-r from-white/[0.05] to-transparent blur-3xl pointer-events-none"></div>
  <div class="absolute -top-20 -right-32 w-96 h-96 border border-border/30 rounded-full opacity-50"></div>
  <div class="absolute bottom-0 -left-16 w-72 h-72 border border-border/30 rounded-full opacity-30"></div>

  <div class="container relative z-10 mx-auto max-w-6xl space-y-16 overflow-x-hidden">
    <div class="grid gap-6 lg:gap-10 lg:grid-cols-[1.15fr_0.85fr]">
      <div class="space-y-8">
        <div class="font-fira text-4xl sm:text-6xl lg:text-[6rem] font-bold leading-[0.9] tracking-tight text-left">
          {{ $hero['titleLeft'] }}
        </div>

        {{-- Mobile-only second title --}}
        <div class="font-fira text-4xl sm:text-6xl font-bold leading-[0.9] tracking-tight text-left lg:hidden">
          {{ $hero['titleRight'] }}
        </div>

        <p class="text-muted-foreground text-base sm:text-lg leading-relaxed max-w-xl">
          {{ $hero['description'] }}
        </p>
      </div>

      <div class="flex flex-col items-end text-right gap-8">
        <div class="flex items-center gap-4">
          <x-button
            variant="secondary"
            :href="esc_url($hero['ctaHref'])"
            class="rounded-full bg-off-white text-background font-fira text-base px-10 py-6 shadow-[inset_0_1px_0_rgba(255,255,255,0.3)] hover:bg-white justify-between gap-10 min-w-[220px]"
          >
            <span class="text-background/90">{{ $hero['ctaLabel'] }}</span>
          </x-button>

          <a
            href="{{ esc_url($hero['ctaHref']) }}"
            class="h-12 w-12 rounded-full border border-border bg-background text-foreground flex items-center justify-center hover:bg-secondary/70 transition"
            aria-label="{{ __('Go to projects', 'edgar-ferreira') }}"
          >
            <x-icon name="arrow-right" class="h-5 w-5" />
          </a>
        </div>

        <div class="hidden lg:block font-fira text-5xl sm:text-6xl lg:text-[6.5rem] font-bold leading-[0.85] tracking-tight text-foreground">
          {{ $hero['titleRight'] }}
        </div>
      </div>
    </div>

    <div class="space-y-6">
      {{-- Social links: two left, one centred, two right --}}
      <div class="w-full max-w-4xl mx-auto">
        <div class="grid grid-cols-3 gap-2 items-center">
          @php
            $groups = [
                'justify-start' => array_slice($hero['socialLinks'], 0, 2),
                'justify-center' => array_slice($hero['socialLinks'], 2, 1),
                'justify-end' => array_slice($hero['socialLinks'], 3, 2),
            ];
          @endphp

          @foreach ($groups as $justify => $links)
            <div class="flex items-center gap-4 {{ $justify }}">
              @foreach ($links as $social)
                <a
                  href="{{ esc_url($social['href']) }}"
                  class="flex items-center gap-2 rounded-full border border-border/50 px-3 sm:px-5 py-2.5 text-sm font-medium tracking-tight transition hover:bg-secondary/70"
                  aria-label="{{ esc_attr($social['label']) }}"
                >
                  <x-icon :name="$social['icon']" class="h-4 w-4" />
                  <span class="hidden sm:inline">{{ $social['label'] }}</span>
                </a>
              @endforeach
            </div>
          @endforeach
        </div>
      </div>

      <div class="flex items-center justify-between">
        <p class="font-fira text-xs tracking-[0.8em] uppercase text-muted-foreground">
          {{ $hero['carouselLabel'] }}
        </p>

        <div class="hidden sm:flex gap-3">
          <button
            type="button"
            data-carousel-prev
            class="h-12 w-12 rounded-full border border-border/60 flex items-center justify-center text-muted-foreground hover:text-foreground hover:bg-secondary/70 transition"
            aria-label="{{ __('Scroll projects left', 'edgar-ferreira') }}"
          >
            <x-icon name="arrow-left" class="h-5 w-5" />
          </button>
          <button
            type="button"
            data-carousel-next
            class="h-12 w-12 rounded-full border border-border/60 flex items-center justify-center text-muted-foreground hover:text-foreground hover:bg-secondary/70 transition"
            aria-label="{{ __('Scroll projects right', 'edgar-ferreira') }}"
          >
            <x-icon name="arrow-right" class="h-5 w-5" />
          </button>
        </div>
      </div>

      {{-- Carousel --}}
      <div
        data-carousel
        class="relative h-[450px] sm:h-[320px] overflow-hidden"
        aria-label="{{ __('Highlighted projects carousel', 'edgar-ferreira') }}"
        aria-live="polite"
      >
        <div class="absolute inset-0 flex items-center justify-center overflow-hidden">
          @foreach ($projects as $index => $project)
            <div
              data-carousel-slide="{{ $index }}"
              class="absolute w-full sm:w-[85%] lg:w-[60%] transition-all duration-500 ease-out"
              style="{{ $slideStyle($index) }}"
            >
              <article class="relative w-full h-[450px] sm:h-[300px] rounded-[40px] overflow-hidden border border-border/30 shadow-[0_20px_60px_rgba(0,0,0,0.45)]">
                <div
                  data-carousel-overlay
                  class="absolute inset-0 bg-black/60 z-10 pointer-events-none {{ $index === 0 ? 'hidden' : '' }}"
                ></div>

                <div class="absolute inset-0 flex flex-col sm:flex-row">
                  <div class="w-full h-[45%] sm:w-[45%] sm:h-full relative">
                    <div
                      class="absolute inset-0 bg-cover bg-center"
                      @if ($project['image']) style="background-image: url('{{ esc_url($project['image']) }}')" @endif
                    ></div>
                  </div>

                  <div class="w-full h-[55%] sm:w-[55%] sm:h-full bg-gradient-to-br from-[#1a1a1a] to-[#0f0f0f] p-6 sm:p-8 flex flex-col justify-between">
                    <div class="space-y-3">
                      <p class="text-xs uppercase tracking-[0.35em] text-gray-500">
                        {{ implode(' • ', $project['tags']) }}
                      </p>
                      {{-- sm:leading-8 mirrors the line-height sm:text-2xl
                           supplied under Tailwind 3; the arbitrary lg size
                           carries none, so leading-tight applies there. --}}
                      <h3 class="font-fira text-xl leading-tight sm:text-2xl sm:leading-8 lg:text-[2rem] lg:leading-tight text-white">
                        {{ $project['title'] }}
                      </h3>
                      <p class="text-xs sm:text-sm text-gray-400 leading-relaxed line-clamp-2">
                        {{ $project['description'] }}
                      </p>
                    </div>

                    <div class="flex items-center gap-3">
                      <a
                        href="{{ esc_url($project['permalink']) }}"
                        class="inline-flex items-center gap-2 rounded-full bg-white px-6 py-2.5 text-sm font-medium text-black transition hover:bg-gray-100"
                      >
                        {{ __('Read more', 'edgar-ferreira') }}
                      </a>
                      <a
                        href="{{ esc_url($project['permalink']) }}"
                        class="flex h-10 w-10 items-center justify-center rounded-full bg-white text-black transition hover:bg-gray-100"
                        aria-label="{{ __('View project details', 'edgar-ferreira') }}"
                      >
                        <x-icon name="arrow-right" class="h-4 w-4" />
                      </a>
                    </div>
                  </div>
                </div>
              </article>
            </div>
          @endforeach
        </div>
      </div>

      {{-- Indicators --}}
      <div class="flex justify-center gap-2 pt-4">
        @foreach ($projects as $index => $project)
          <button
            type="button"
            data-carousel-dot="{{ $index }}"
            class="h-2 rounded-full transition-all duration-300 {{ $index === 0 ? 'w-8 bg-foreground' : 'w-2 bg-foreground/30 hover:bg-foreground/50' }}"
            aria-label="{{ sprintf(__('Go to project %d', 'edgar-ferreira'), $index + 1) }}"
          ></button>
        @endforeach
      </div>
    </div>
  </div>
</section>
