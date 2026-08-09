<!doctype html>
<html @php(language_attributes())>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500;600;700&family=Open+Sans:wght@300;400;600;700&display=swap">

    @php(do_action('get_header'))
    @php(wp_head())

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @include('partials.analytics')
  </head>

  <body @php(body_class('bg-background text-foreground antialiased'))>
    @php(wp_body_open())

    <a class="sr-only focus:not-sr-only focus:absolute focus:z-[60] focus:m-4 focus:rounded-lg focus:bg-card focus:px-4 focus:py-2" href="#main">
      {{ __('Skip to content', 'edgar-ferreira') }}
    </a>

    @include('sections.navigation')

    <main id="main" class="relative">
      @yield('content')
    </main>

    @php(do_action('get_footer'))
    @php(wp_footer())
  </body>
</html>
