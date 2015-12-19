<p class="alert alert-info ranking-info overall-ranking">
    <strong>Wzór obliczania:</strong><br />
    <i>RANKING</i> = Liczba zmian + 2 * Liczba sprawdzonych zmian - 0.5 * :iczba zmian NT
    - 0.1 * Liczba otrzymanych komentarzy + 0.05 * Liczba napisanych komentarzy
    + 0.05 * Średnia długość komentarza - 0.15 * Liczba komentarzy na liczbę zmian
</p>
<ul class="list-group ranking result-list" data-project="{{ $project->getAttribute('id') }}">
    @foreach ($results as $result)
        <li class="list-group-item clearfix">
            <img src="{{ $result['avatar']->url }}" height="{{ $result['avatar']->height }}" class="avatar" />
            <strong>{{ $result['name'] }}</strong>
            <span class="ranking-result">{{ $result['value'] }}</span>
        </li>
    @endforeach
</ul>

@section('stylesheets')
    <link rel="stylesheet" href="{{ asset('/css/ranking.css') }}" type="text/css">
    @parent
@endsection

