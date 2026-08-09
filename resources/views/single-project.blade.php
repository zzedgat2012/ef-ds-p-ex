@extends('layouts.app')

@section('content')
  <div class="min-h-screen">
    <div
      class="reveal-section container mx-auto px-4 pt-32 pb-20 max-w-4xl"
      style="transition-delay: 120ms"
    >
      <x-button variant="ghost" :href="home_url('/#projects')" class="mb-8 font-fira">
        <x-icon name="arrow-left" class="mr-2 w-4 h-4" />
        {{ __('Back to projects', 'edgar-ferreira') }}
      </x-button>

      <div class="mb-12">
        <h1 class="font-fira text-4xl md:text-5xl font-bold mb-6 text-foreground">
          {{ $project['title'] }}
        </h1>

        @if ($project['tags'])
          <div class="flex flex-wrap gap-2 mb-6">
            @foreach ($project['tags'] as $tag)
              <span class="px-3 py-1 bg-secondary rounded-full text-sm font-fira">{{ $tag }}</span>
            @endforeach
          </div>
        @endif

        <div class="flex gap-4">
          @if ($project['demoUrl'])
            <x-button :href="esc_url($project['demoUrl'])" class="font-fira" target="_blank" rel="noopener noreferrer">
              <x-icon name="external-link" class="mr-2 w-4 h-4" />
              {{ __('View Demo', 'edgar-ferreira') }}
            </x-button>
          @endif

          @if ($project['githubUrl'])
            <x-button variant="outline" :href="esc_url($project['githubUrl'])" class="font-fira" target="_blank" rel="noopener noreferrer">
              <x-icon name="github" class="mr-2 w-4 h-4" />
              {{ __('View Code', 'edgar-ferreira') }}
            </x-button>
          @endif
        </div>
      </div>

      @if ($project['image'])
        <div class="mb-12 rounded-2xl overflow-hidden glow-effect">
          <img
            src="{{ esc_url($project['image']) }}"
            alt="{{ esc_attr($project['title']) }}"
            class="w-full aspect-video object-cover"
          >
        </div>
      @endif

      <div class="prose prose-invert max-w-none mb-12">
        <h2 class="font-fira text-2xl mb-4 text-foreground">{{ __('Overview', 'edgar-ferreira') }}</h2>
        <div class="text-muted-foreground leading-relaxed">
          {!! $project['longDescription'] !!}
        </div>
      </div>

      @if ($project['features'])
        <div class="mb-12">
          <h2 class="font-fira text-2xl mb-6 text-foreground">{{ __('Key Features', 'edgar-ferreira') }}</h2>
          <div class="grid md:grid-cols-2 gap-4">
            @foreach ($project['features'] as $feature)
              <div class="bg-card p-4 rounded-lg border border-border hover:border-accent transition-colors">
                <p class="text-sm text-foreground">{{ $feature }}</p>
              </div>
            @endforeach
          </div>
        </div>
      @endif

      @if ($project['technologies'])
        <div>
          <h2 class="font-fira text-2xl mb-6 text-foreground">{{ __('Technologies Used', 'edgar-ferreira') }}</h2>
          <div class="flex flex-wrap gap-3">
            @foreach ($project['technologies'] as $technology)
              <span class="px-4 py-2 bg-secondary rounded-lg text-sm font-fira hover:bg-accent transition-colors">
                {{ $technology }}
              </span>
            @endforeach
          </div>
        </div>
      @endif
    </div>
  </div>
@endsection
