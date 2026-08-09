<section
  id="about"
  class="reveal-section py-20 px-4 relative"
  style="transition-delay: 140ms"
>
  <div class="container mx-auto max-w-6xl">
    <div class="mb-16 text-left">
      <h2 class="font-fira text-xs text-muted-foreground tracking-[0.8em] uppercase">
        {{ $about['title'] }}
      </h2>
    </div>

    <div class="grid lg:grid-cols-2 gap-12 items-center">
      {{-- Portrait --}}
      <div class="flex justify-center lg:justify-start order-2 lg:order-1">
        <div class="w-full max-w-md aspect-[3/4] bg-card rounded-3xl overflow-hidden glow-effect">
          @if ($about['imageId'])
            {!! wp_get_attachment_image($about['imageId'], 'large', false, [
                'class' => 'w-full h-full object-cover grayscale hover:grayscale-0 transition-all duration-500',
                'alt' => __('Developer portrait', 'edgar-ferreira'),
            ]) !!}
          @elseif ($about['image'])
            <img
              src="{{ esc_url($about['image']) }}"
              alt="{{ __('Developer portrait', 'edgar-ferreira') }}"
              class="w-full h-full object-cover grayscale hover:grayscale-0 transition-all duration-500"
            >
          @endif
        </div>
      </div>

      {{-- Copy --}}
      <div class="space-y-8 order-1 lg:order-2">
        <div class="space-y-4">
          <p class="text-xl leading-relaxed text-foreground">
            {{ $about['greeting'] }}
            <span class="font-fira font-semibold">{{ $about['name'] }}</span>,
            {{ $about['roleSummary'] }}
          </p>

          <p class="text-base leading-relaxed text-muted-foreground">
            {{ __('With over', 'edgar-ferreira') }}
            <span class="text-foreground font-semibold">{{ $about['experienceYears'] }}</span>,
            {{ $about['experienceText'] }}
          </p>

          <p class="text-base leading-relaxed text-muted-foreground">
            {{ $about['principlesText'] }}
          </p>
        </div>

        @if ($about['skills'])
          <div>
            <h3 class="font-fira text-sm text-foreground mb-4">
              {{ __('Technologies & Tools', 'edgar-ferreira') }}
            </h3>
            <div class="flex flex-wrap gap-2">
              @foreach ($about['skills'] as $skill)
                <span class="px-3 py-1.5 bg-secondary rounded-lg text-xs font-fira hover:bg-accent hover:scale-105 transition-all cursor-default">
                  {{ $skill }}
                </span>
              @endforeach
            </div>
          </div>
        @endif

        @if ($about['whatIDo'])
          <div class="pt-4">
            <h3 class="font-fira text-sm text-foreground mb-3">
              {{ __('What I Do', 'edgar-ferreira') }}
            </h3>
            <ul class="space-y-2 text-sm text-muted-foreground">
              @foreach ($about['whatIDo'] as $item)
                <li class="flex items-start">
                  <span class="text-accent mr-2">&#9656;</span>
                  {{ $item }}
                </li>
              @endforeach
            </ul>
          </div>
        @endif

        @if ($about['education'])
          <div class="pt-4">
            <h3 class="font-fira text-sm text-foreground mb-3">
              {{ $about['educationHeading'] }}
            </h3>
            <ul class="space-y-2 text-sm text-muted-foreground">
              @foreach ($about['education'] as $item)
                <li class="flex items-start">
                  <span class="text-accent mr-2">&#9656;</span>
                  {{ $item }}
                </li>
              @endforeach
            </ul>
          </div>
        @endif

        @if ($about['certifications'])
          <div class="pt-4">
            <h3 class="font-fira text-sm text-foreground mb-3">
              {{ $about['certificationsHeading'] }}
            </h3>
            <ul class="space-y-2 text-sm text-muted-foreground">
              @foreach ($about['certifications'] as $item)
                <li class="flex items-start">
                  <span class="text-accent mr-2">&#9656;</span>
                  {{ $item }}
                </li>
              @endforeach
            </ul>
          </div>
        @endif
      </div>
    </div>
  </div>
</section>
