{{--
    Print stylesheet for the CV, mirroring the StyleSheet in the React app's
    CVDocument. Rendered by mpdf, so only inline/simple CSS is used.
--}}
<style>
    body { font-family: sans-serif; color: #555555; font-size: 10pt; }
    .header { border-bottom: 1px solid #E5E5E5; padding-bottom: 12pt; margin-bottom: 18pt; }
    .name { font-family: monospace; font-size: 24pt; color: #121212; margin: 0 0 6pt; }
    .role { font-size: 14pt; color: #666666; margin: 0 0 3pt; }
    .contact { font-size: 10pt; color: #888888; margin: 0; }
    .section { margin-bottom: 16pt; }
    .section-title {
        font-family: monospace;
        font-size: 16pt;
        color: #121212;
        margin: 0 0 10pt;
        text-transform: uppercase;
        letter-spacing: 1pt;
    }
    .description { font-size: 10pt; color: #555555; line-height: 1.5; margin: 0 0 6pt; }
    .experience { margin-bottom: 14pt; }
    .company { font-size: 14pt; font-weight: bold; color: #121212; }
    .period { font-size: 10pt; color: #666666; font-family: monospace; }
    .position { font-size: 12pt; color: #333333; margin: 3pt 0 0; }
    .bullet-row { margin-bottom: 5pt; font-size: 10pt; color: #555555; line-height: 1.4; }
    .bullet { color: #999999; font-family: monospace; }
</style>

<div class="header">
    <h1 class="name">{{ $name }}</h1>
    <p class="role">{{ $role }}</p>
    @if ($contactLine)
        <p class="contact">{{ $contactLine }}</p>
    @endif
</div>

<div class="section">
    <h2 class="section-title">{{ __('Summary', 'edgar-ferreira') }}</h2>
    <p class="description">
        {{ $about['greeting'] }} {{ $about['name'] }}, {{ $about['roleSummary'] }}
    </p>
    <p class="description">
        {{ __('With over', 'edgar-ferreira') }} {{ $about['experienceYears'] }}, {{ $about['experienceText'] }}
    </p>
    <p class="description">{{ $about['principlesText'] }}</p>
</div>

@if ($about['skills'])
    <div class="section">
        <h2 class="section-title">{{ __('Skills', 'edgar-ferreira') }}</h2>
        <p class="description">{{ implode(' • ', $about['skills']) }}</p>
    </div>
@endif

@if ($work['experiences'])
    <div class="section">
        <h2 class="section-title">{{ $work['heading'] }}</h2>

        @foreach ($work['experiences'] as $experience)
            <div class="experience">
                <table width="100%"><tr>
                    <td><span class="company">{{ $experience['company'] }}</span></td>
                    <td align="right"><span class="period">{{ $experience['period'] }}</span></td>
                </tr></table>
                <p class="position">{{ $experience['position'] }}</p>
            </div>
        @endforeach
    </div>
@endif

@if ($about['education'])
    <div class="section">
        <h2 class="section-title">{{ $about['educationHeading'] }}</h2>
        @foreach ($about['education'] as $item)
            <p class="bullet-row"><span class="bullet">&#9656;</span> {{ $item }}</p>
        @endforeach
    </div>
@endif

@if ($about['certifications'])
    <div class="section">
        <h2 class="section-title">{{ $about['certificationsHeading'] }}</h2>
        @foreach ($about['certifications'] as $item)
            <p class="bullet-row"><span class="bullet">&#9656;</span> {{ $item }}</p>
        @endforeach
    </div>
@endif
