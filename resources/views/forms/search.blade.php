<form role="search" method="get" class="search-form" action="{{ home_url('/') }}">
  <label>
    <span class="sr-only">
      {{ _x('Search for:', 'label', 'edgar-ferreira') }}
    </span>

    <input
      type="search"
      placeholder="{!! esc_attr_x('Search &hellip;', 'placeholder', 'edgar-ferreira') !!}"
      value="{!! get_search_query() !!}"
      name="s"
    >
  </label>

  <button>{{ _x('Search', 'submit button', 'edgar-ferreira') }}</button>
</form>
