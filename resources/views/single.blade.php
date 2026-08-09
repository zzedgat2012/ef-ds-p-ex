@extends('layouts.app')

@section('content')
  <div class="min-h-screen bg-background">
    <article
      class="reveal-section container mx-auto px-4 pt-32 pb-20 max-w-4xl"
      style="transition-delay: 120ms"
    >
      <x-button variant="ghost" :href="home_url('/#articles')" class="mb-8 font-fira">
        <x-icon name="arrow-left" class="mr-2 w-4 h-4" />
        {{ __('Back to articles', 'edgar-ferreira') }}
      </x-button>

      <header class="mb-16 pb-12 border-b border-border/30">
        @if ($article['tags'])
          <div class="flex flex-wrap gap-2 mb-6">
            @foreach ($article['tags'] as $tag)
              <span class="px-4 py-1.5 bg-secondary/60 border border-border/40 rounded-full text-xs font-fira">
                {{ $tag }}
              </span>
            @endforeach
          </div>
        @endif

        <h1 class="font-fira text-5xl md:text-6xl font-bold mb-8 text-foreground leading-[1.1]">
          {{ $article['title'] }}
        </h1>

        <div class="flex flex-wrap items-center gap-6 text-sm text-muted-foreground mb-8">
          <div class="flex items-center gap-2">
            <x-icon name="calendar" class="w-4 h-4" />
            <time datetime="{{ $article['date'] }}">{{ $article['dateFormatted'] }}</time>
          </div>

          @if ($article['readTime'])
            <div class="flex items-center gap-2">
              <x-icon name="clock" class="w-4 h-4" />
              <span>{{ $article['readTime'] }}</span>
            </div>
          @endif
        </div>

        <div class="flex items-center gap-4">
          <img
            src="{{ esc_url($article['author']['avatar']) }}"
            alt="{{ esc_attr($article['author']['name']) }}"
            class="w-14 h-14 rounded-full border-2 border-border/40"
          >
          <div>
            <p class="font-fira text-base text-foreground font-semibold">{{ $article['author']['name'] }}</p>
            <p class="text-sm text-muted-foreground">{{ __('Product Engineer', 'edgar-ferreira') }}</p>
          </div>
        </div>
      </header>

      <div class="prose prose-invert max-w-none article-content">
        {!! $article['content'] !!}
      </div>

      {{-- The React version rendered these three as inert buttons; they are
           wired up here so the markup keeps its meaning. --}}
      <div class="mt-16 pt-8 border-t border-border/30">
        <p class="text-sm text-muted-foreground mb-4">{{ __('Share this article', 'edgar-ferreira') }}</p>
        <div class="flex gap-3">
          <a
            href="https://twitter.com/intent/tweet?url={{ rawurlencode($article['permalink']) }}&text={{ rawurlencode($article['title']) }}"
            target="_blank"
            rel="noopener noreferrer"
            class="px-4 py-2 bg-secondary/60 hover:bg-secondary rounded-lg text-sm font-fira transition-colors"
          >
            {{ __('Twitter', 'edgar-ferreira') }}
          </a>
          <a
            href="https://www.linkedin.com/sharing/share-offsite/?url={{ rawurlencode($article['permalink']) }}"
            target="_blank"
            rel="noopener noreferrer"
            class="px-4 py-2 bg-secondary/60 hover:bg-secondary rounded-lg text-sm font-fira transition-colors"
          >
            {{ __('LinkedIn', 'edgar-ferreira') }}
          </a>
          <button
            type="button"
            data-copy-link="{{ esc_url($article['permalink']) }}"
            data-copied-label="{{ esc_attr__('Copied!', 'edgar-ferreira') }}"
            class="px-4 py-2 bg-secondary/60 hover:bg-secondary rounded-lg text-sm font-fira transition-colors"
          >
            {{ __('Copy Link', 'edgar-ferreira') }}
          </button>
        </div>
      </div>

      <footer class="mt-16 pt-8 border-t border-border/30">
        <div class="bg-card/60 border border-border/40 rounded-2xl p-8">
          <div class="flex items-start gap-6">
            <img
              src="{{ esc_url($article['author']['avatar']) }}"
              alt="{{ esc_attr($article['author']['name']) }}"
              class="w-20 h-20 rounded-full border-2 border-border/40"
            >
            <div class="flex-1">
              <p class="font-fira text-xl text-foreground font-semibold mb-2">
                {{ sprintf(__('Written by %s', 'edgar-ferreira'), $article['author']['name']) }}
              </p>
              <p class="text-sm text-muted-foreground mb-4">
                {{ __('Product engineer passionate about shipping scalable applications with clean, maintainable code.', 'edgar-ferreira') }}
              </p>
              <x-button variant="outline" :href="home_url('/#articles')" class="font-fira">
                {{ __('Read more articles', 'edgar-ferreira') }}
              </x-button>
            </div>
          </div>
        </div>
      </footer>
    </article>
  </div>
@endsection
