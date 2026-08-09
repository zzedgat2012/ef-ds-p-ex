@extends('layouts.app')

@section('content')
  <div class="relative">
    @include('sections.hero')
    @include('sections.about')
    @include('sections.work')
    @include('sections.projects')
    @include('sections.articles')
    @include('sections.contact')
  </div>
@endsection
