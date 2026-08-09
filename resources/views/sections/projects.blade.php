@php
    // Badge glyphs are positional in the React component, cycling after four.
    $badges = ['&#127912;', '&#9889;', '&#128640;', '&#128187;'];
@endphp

<section
  id="projects"
  class="reveal-section py-20 px-4 relative"
  style="transition-delay: 220ms"
>
  <div class="container mx-auto max-w-6xl">
    <div class="mb-16 text-left">
      <h2 class="font-fira text-xs text-muted-foreground tracking-[0.8em] uppercase">
        {{ $section['title'] }}
      </h2>
    </div>

    <div class="space-y-60">
      @foreach ($projects as $index => $project)
        <article class="group">
          <div class="grid md:grid-cols-2 gap-8 md:gap-12 items-center">
            {{-- Copy --}}
            <div class="space-y-6">
              {{-- md:leading-10 restores the line-height text-4xl carried on
                   its own under Tailwind 3, where the responsive font-size
                   utility overrode leading-tight. --}}
              <h3 class="font-fira text-3xl leading-tight md:text-4xl md:leading-10 text-foreground">
                {{ $project['title'] }}
              </h3>

              @if ($project['tags'])
                <div class="flex flex-wrap gap-2">
                  @foreach ($project['tags'] as $tag)
                    <span class="px-4 py-2 bg-secondary/60 hover:bg-secondary rounded-full text-xs font-fira text-foreground border border-border/30 transition-colors">
                      {{ $tag }}
                    </span>
                  @endforeach
                </div>
              @endif

              <p class="text-base text-muted-foreground leading-relaxed">
                {{ $project['description'] }}
              </p>

              @if ($project['longDescription'])
                <div class="text-sm text-muted-foreground leading-relaxed">
                  {!! $project['longDescription'] !!}
                </div>
              @endif

              <div class="flex items-center gap-3 pt-4">
                <a
                  href="{{ esc_url($project['permalink']) }}"
                  class="inline-flex items-center gap-2 rounded-full bg-white px-6 py-3 text-sm font-medium text-background transition hover:bg-gray-100"
                >
                  {{ __('Read more', 'edgar-ferreira') }}
                </a>
                <a
                  href="{{ esc_url($project['permalink']) }}"
                  class="flex h-12 w-12 items-center justify-center rounded-full bg-white text-background transition hover:bg-gray-100"
                  aria-label="{{ __('View project details', 'edgar-ferreira') }}"
                >
                  <x-icon name="arrow-right" class="h-5 w-5" />
                </a>
              </div>
            </div>

            {{-- Artwork --}}
            <div class="relative">
              <div class="relative overflow-hidden rounded-2xl bg-secondary/20">
                <div class="relative aspect-video overflow-hidden">
                  @if ($project['image'])
                    <img
                      src="{{ esc_url($project['image']) }}"
                      alt="{{ esc_attr($project['title']) }}"
                      class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                      loading="lazy"
                    >
                  @endif
                </div>
              </div>

              <div class="absolute -bottom-6 -left-6 h-16 w-16 rounded-2xl bg-card border border-border/40 flex items-center justify-center shadow-lg">
                <span class="text-2xl">{!! $badges[$index % count($badges)] !!}</span>
              </div>
            </div>
          </div>
        </article>
      @endforeach
    </div>

    <div class="mt-20 flex items-center justify-between border-t border-border/30 pt-8">
      <p class="text-sm text-muted-foreground">
        {{ __('View all projects on', 'edgar-ferreira') }}
        <a
          href="{{ esc_url($section['ctaHref']) }}"
          class="text-foreground hover:text-accent transition-colors font-fira font-semibold"
        >
          {{ $section['ctaLabel'] }} &rarr;
        </a>
      </p>
      <p class="font-fira text-xs text-muted-foreground uppercase tracking-wider">
        {{ sprintf(_n('%d Project', '%d Projects', count($projects), 'edgar-ferreira'), count($projects)) }}
      </p>
    </div>
  </div>
</section>
