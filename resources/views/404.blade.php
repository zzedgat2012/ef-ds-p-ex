@extends('layouts.app')

@section('content')
  @php
    $path = wp_parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
  @endphp

  <div class="reveal-section min-h-screen flex items-center justify-center px-4 relative overflow-hidden">
    {{-- Background accents, mirroring the hero --}}
    <div class="absolute inset-y-0 left-1/2 w-[640px] h-[640px] -translate-x-1/2 bg-gradient-to-r from-white/[0.05] to-transparent blur-3xl pointer-events-none"></div>
    <div class="absolute top-20 -right-32 w-96 h-96 border border-border/30 rounded-full opacity-50"></div>
    <div class="absolute bottom-20 -left-16 w-72 h-72 border border-border/30 rounded-full opacity-30"></div>

    <div class="container relative z-10 mx-auto max-w-4xl">
      <div class="text-center space-y-12">
        {{-- Layered 404 with the periodic glitch offset driven by app.js --}}
        <div class="relative" data-glitch>
          <h1
            data-glitch-back
            class="font-fira text-[12rem] sm:text-[16rem] md:text-[20rem] font-bold leading-none tracking-tighter text-foreground/10 select-none transition-all duration-200"
          >
            404
          </h1>
          <div class="absolute inset-0 flex items-center justify-center">
            <h2
              data-glitch-front
              class="font-fira text-6xl sm:text-7xl md:text-8xl font-bold text-foreground transition-all duration-200"
            >
              404
            </h2>
          </div>
        </div>

        <div class="space-y-4">
          <p class="font-fira text-xs text-muted-foreground tracking-[0.8em] uppercase">
            {{ __('Page not found', 'edgar-ferreira') }}
          </p>
          <p class="text-xl sm:text-2xl text-foreground max-w-2xl mx-auto">
            {{ __("Looks like you've ventured into the", 'edgar-ferreira') }}
            <span class="text-accent italic">{{ __('void', 'edgar-ferreira') }}</span>.
            <br>
            {{ __("The page you're looking for doesn't exist.", 'edgar-ferreira') }}
          </p>

          @if ($path !== '/')
            <div class="pt-4">
              <div class="inline-flex items-center gap-2 px-4 py-2 bg-secondary/50 rounded-lg text-xs font-mono text-muted-foreground border border-border/40">
                <x-icon name="search" class="h-3 w-3" />
                <span class="opacity-60">{{ __('Attempted path:', 'edgar-ferreira') }}</span>
                <span class="text-foreground">{{ $path }}</span>
              </div>
            </div>
          @endif
        </div>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-4 pt-8">
          <a
            href="{{ home_url('/') }}"
            class="group inline-flex items-center gap-3 rounded-full bg-white px-8 py-4 text-base font-medium text-background transition hover:bg-gray-100 shadow-lg"
          >
            <x-icon name="home" class="h-5 w-5" />
            {{ __('Back to Home', 'edgar-ferreira') }}
          </a>
          <button
            type="button"
            data-history-back
            class="group inline-flex items-center gap-3 rounded-full border border-border bg-background px-8 py-4 text-base font-medium text-foreground transition hover:bg-secondary/70"
          >
            {{ __('Go Back', 'edgar-ferreira') }}
            <x-icon name="arrow-right" class="h-5 w-5 rotate-180 group-hover:-translate-x-1 transition-transform" />
          </button>
        </div>

        <div class="pt-12">
          <p class="text-sm text-muted-foreground mb-4">
            {{ __('You might be looking for:', 'edgar-ferreira') }}
          </p>
          <div class="flex flex-wrap justify-center gap-3">
            @foreach ([
                ['label' => __('Projects', 'edgar-ferreira'), 'href' => home_url('/#projects')],
                ['label' => __('About', 'edgar-ferreira'), 'href' => home_url('/#about')],
                ['label' => __('Work', 'edgar-ferreira'), 'href' => home_url('/#work')],
            ] as $link)
              <a
                href="{{ esc_url($link['href']) }}"
                class="px-4 py-2 bg-card/60 hover:bg-card border border-border/40 rounded-full text-sm font-fira text-foreground hover:text-accent transition-all"
              >
                {{ $link['label'] }}
              </a>
            @endforeach
          </div>
        </div>

        <div class="pt-8 opacity-40 hover:opacity-100 transition-opacity">
          <p class="text-xs font-mono text-muted-foreground">
            {{ sprintf('Error #%04d', wp_rand(0, 999)) }} &bull;
            Timestamp: {{ gmdate('c') }}
          </p>
        </div>
      </div>
    </div>
  </div>
@endsection
