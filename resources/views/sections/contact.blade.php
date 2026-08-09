<section
  id="contact"
  class="reveal-section py-20 px-4 relative"
  style="transition-delay: 300ms"
>
  <div class="container mx-auto max-w-6xl">
    <div class="mb-16 text-left">
      <h2 class="font-fira text-xs text-muted-foreground tracking-[0.8em] uppercase">
        {{ $contact['title'] }}
      </h2>
    </div>

    <div class="grid lg:grid-cols-2 gap-12 items-start">
      <div>
        <h3 class="font-fira text-5xl sm:text-6xl font-bold text-foreground mb-4">
          @foreach (preg_split('/\s+/', trim($contact['name'])) as $segment)
            <span class="block">{{ $segment }}</span>
          @endforeach
        </h3>
        <p class="text-muted-foreground text-lg mb-8">{{ $contact['titleSuffix'] }}</p>
      </div>

      <div class="space-y-8">
        <div class="space-y-2">
          @foreach ($contact['contactInfo'] as $info)
            <p class="text-sm text-muted-foreground">
              {{ $info['label'] }}
              @if ($info['value'])
                <br>
                <span class="text-foreground">{{ $info['value'] }}</span>
              @endif
            </p>
          @endforeach
        </div>

        <div class="grid grid-cols-2 gap-4">
          @foreach ($contact['socialLinks'] as $social)
            <a
              href="{{ esc_url($social['href']) }}"
              class="flex items-center gap-3 p-4 bg-card rounded-lg hover:bg-secondary hover:scale-105 transition-all group"
            >
              <x-icon :name="$social['icon']" class="w-5 h-5 text-muted-foreground group-hover:text-foreground transition-colors" />
              <span class="text-sm font-fira text-muted-foreground group-hover:text-foreground transition-colors">
                {{ $social['label'] }}
              </span>
            </a>
          @endforeach
        </div>
      </div>
    </div>

    <div class="mt-20 pt-8 border-t border-border text-center">
      <p class="text-xs text-muted-foreground">
        {{ $contact['footer'] }}
      </p>
    </div>
  </div>
</section>
