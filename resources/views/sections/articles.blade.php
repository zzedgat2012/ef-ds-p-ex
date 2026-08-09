<section
  id="articles"
  class="reveal-section py-20 px-4 relative"
  style="transition-delay: 260ms"
>
  <div class="container mx-auto max-w-6xl">
    <div class="text-right mb-12">
      <h2 class="font-fira text-4xl sm:text-7xl md:text-8xl font-bold text-foreground">
        {{ $section['heading'] }}
      </h2>
    </div>

    <div class="grid md:grid-cols-2 gap-6">
      @foreach ($articles as $article)
        <div class="bg-card rounded-2xl p-8 hover:glow-effect transition-all group">
          <h3 class="font-fira text-lg mb-3 text-foreground">{{ $article['title'] }}</h3>

          <p class="text-sm text-muted-foreground mb-6 leading-relaxed">
            {{ $article['description'] }}
          </p>

          <x-button
            variant="secondary"
            :href="esc_url($article['permalink'])"
            class="group-hover:bg-foreground group-hover:text-background rounded-full font-fira"
          >
            {{ __('Read more', 'edgar-ferreira') }}
            <x-icon name="arrow-right" class="ml-2 group-hover:translate-x-1 transition-transform" />
          </x-button>
        </div>
      @endforeach
    </div>
  </div>
</section>
