<section
  id="work"
  class="reveal-section py-20 px-4 relative"
  style="transition-delay: 180ms"
>
  <div class="container mx-auto max-w-6xl">
    <div class="flex flex-col md:flex-row justify-between items-end mb-12 gap-6">
      <div class="text-right w-full">
        <h2 class="font-fira text-4xl sm:text-7xl md:text-8xl font-bold text-foreground break-words mb-6">
          {{ $work['heading'] }}
        </h2>

        <x-button variant="outline" :href="home_url('/cv.pdf')" class="font-fira gap-2">
          <x-icon name="download" class="w-4 h-4" />
          {{ __('Download CV', 'edgar-ferreira') }}
        </x-button>
      </div>
    </div>

    <div class="space-y-3">
      @foreach ($work['experiences'] as $experience)
        @php
          // Each row opens a pre-filled request for a work declaration, as in
          // the React component.
          $mailto = sprintf(
              'mailto:%s?subject=%s&body=%s',
              $businessEmail,
              rawurlencode(__('Request work declaration', 'edgar-ferreira')),
              rawurlencode(sprintf(
                  /* translators: 1: job position, 2: company name */
                  __('Hello, I would like to request a work declaration for the position of %1$s at %2$s.', 'edgar-ferreira'),
                  $experience['position'],
                  $experience['company']
              ))
          );
        @endphp

        <a
          href="{{ $mailto }}"
          class="block group relative bg-card/60 hover:bg-card border border-border/40 hover:border-border transition-all duration-300 rounded-2xl overflow-hidden cursor-pointer"
        >
          <div class="absolute inset-0 bg-gradient-to-r from-accent/0 via-accent/5 to-accent/0 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>

          <div class="relative p-6 sm:p-8">
            <div class="grid grid-cols-1 sm:grid-cols-12 gap-4 sm:gap-6 items-center">
              <div class="sm:col-span-3">
                <p class="font-fira text-base sm:text-lg text-foreground font-semibold mb-1">
                  {{ $experience['period'] }}
                </p>
                <p class="font-fira text-xs text-muted-foreground group-hover:text-foreground/70 transition-colors">
                  {{ $experience['duration'] }}
                </p>
              </div>

              <div class="sm:col-span-4">
                <p class="font-fira text-lg sm:text-xl text-foreground font-bold group-hover:text-accent transition-colors">
                  {{ $experience['company'] }}
                </p>
              </div>

              <div class="sm:col-span-5 flex items-center justify-between">
                <p class="text-sm sm:text-base text-muted-foreground group-hover:text-foreground transition-colors font-mono">
                  {{ $experience['position'] }}
                </p>

                <div class="ml-4 opacity-0 group-hover:opacity-100 group-hover:translate-x-0 -translate-x-2 transition-all duration-300">
                  <svg class="w-5 h-5 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                  </svg>
                </div>
              </div>
            </div>
          </div>

          <div class="h-1 bg-gradient-to-r from-transparent via-accent to-transparent scale-x-0 group-hover:scale-x-100 transition-transform duration-500"></div>
        </a>
      @endforeach
    </div>

    <div class="mt-12 text-right">
      <p class="text-sm text-muted-foreground mb-1">
        {{ $work['summaryLabel'] }}
      </p>
      <p class="font-fira text-foreground text-2xl font-semibold">
        {{ $work['summaryValue'] }}
      </p>
    </div>
  </div>
</section>
