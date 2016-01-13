@extends('app')

@section('content')
<div class="col-md-12">
    {!! Form::open(['route' => ['review.generate', $project->id]]) !!}
    @if ($errors->any())
        <div class="alert alert-danger"><strong>Błąd!</strong> Formularz zawiera błędy.</div>
    @endif
    <div class="col-md-2 result-project"><h3>{{ $project->label }}</h3></div>
    <div class="col-md-4">
        <div class="form-group @if ($errors->has('from')) has-error has-feedback @endif">
            {!! Form::text('from', $from, ['class' => 'form-control', 'title' => 'Data początkowa:']) !!}
            @if ($errors->has('from'))
                <span class="glyphicon glyphicon-exclamation-sign form-control-feedback" aria-hidden="true"></span>
                @foreach ($errors->get('from') as $error)
                    <span class="help-block">{{ $error }}</span>
                @endforeach
            @endif
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group @if ($errors->has('to')) has-error has-feedback @endif">
            {!! Form::text('to', $to, ['class' => 'form-control', 'title' => 'Data końcowa:']) !!}
            @if ($errors->has('to'))
                <span class="glyphicon glyphicon-exclamation-sign form-control-feedback" aria-hidden="true"></span>
                @foreach ($errors->get('to') as $error)
                    <span class="help-block">{{ $error }}</span>
                @endforeach
            @endif
        </div>
    </div>
    <div class="col-md-2">
        {!! Form::submit('Pobierz dane', ['class' => 'btn btn-primary form-control']) !!}
    </div>
    {!! Form::close() !!}
</div>
<div class="col-md-12">
    <h2>Wyniki ({{ $from }} - {{ $to }})</h2>
    <div class="row">
        <div class="col-md-2">
            <ul class="nav nav-tabs tabs-left">
                <li class="active"><a href="#ranking" id="tab-ranking" data-toggle="tab">Ranking</a></li>
                @foreach ($tabs as $tab => $label)
                    <li><a href="#{{ $tab }}" id="tab-{{ $tab }}" data-toggle="tab">{{ $label }}</a></li>
                @endforeach
            </ul>
        </div>
        <div class="col-md-10">
            <div class="tab-content">
                <div class="tab-pane active" id="ranking">
                    @foreach ($rankers as $type => $ranker)
                        <div class="col-md-6">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h3 class="panel-title">{!! $ranker->getLabel() !!}</h3>
                                </div>
                                {!! $ranker->getResults($results['ranking_'.$type], $project) !!}
                            </div>
                        </div>
                    @endforeach
                </div>
                @foreach ($tabs as $tab => $label)
                    <div class="tab-pane" id="{{ $tab }}">
                        @foreach ($analyzers[$tab] as $type => $analyzer)
                            <div class="col-md-6">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h3 class="panel-title">{{ $analyzer->getLabel($results[$type]) }}</h3>
                                    </div>
                                    {!! $analyzer->getResults($results[$type], $project) !!}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection

@section('javascripts')
    <script src="{{ asset('/js/app.js') }}" type="text/javascript"></script>
    <script src="{{ asset('/js/analyze.js') }}" type="text/javascript"></script>
    <script src="{{ asset('/js/configure.js') }}" type="text/javascript"></script>
    <script src="{{ asset('/js/chart.js') }}" type="text/javascript"></script>
    <script src="{{ asset('/js/components/prism.js') }}" type="text/javascript" data-manual></script>
    <script src="{{ asset('/js/components/prism-line-numbers.min.js') }}" type="text/javascript" data-manual></script>
    <script src="{{ asset('/js/components/prism-line-highlight.min.js') }}" type="text/javascript" data-manual></script>
    <script src="{{ asset('/js/components/prism/prism-coffeescript.min.js') }}" type="text/javascript" data-manual></script>
    <script src="{{ asset('/js/components/prism/prism-css.min.js') }}" type="text/javascript" data-manual></script>
    <script src="{{ asset('/js/components/prism/prism-java.min.js') }}" type="text/javascript" data-manual></script>
    <script src="{{ asset('/js/components/prism/prism-javascript.min.js') }}" type="text/javascript" data-manual></script>
    <script src="{{ asset('/js/components/prism/prism-less.min.js') }}" type="text/javascript" data-manual></script>
    <script src="{{ asset('/js/components/prism/prism-markup.min.js') }}" type="text/javascript" data-manual></script>
    <script src="{{ asset('/js/components/prism/prism-php.min.js') }}" type="text/javascript" data-manual></script>
    <script src="{{ asset('/js/components/prism/prism-python.min.js') }}" type="text/javascript" data-manual></script>
    <script src="{{ asset('/js/components/prism/prism-scss.min.js') }}" type="text/javascript" data-manual></script>
    <script src="{{ asset('/js/components/prism/prism-sql.min.js') }}" type="text/javascript" data-manual></script>
    <script src="{{ asset('/js/components/prism/prism-yaml.min.js') }}" type="text/javascript" data-manual></script>
    @parent
@endsection

@section('stylesheets')
    <link rel="stylesheet" href="{{ asset('/css/graph.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('/css/chart.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('/css/components/prism.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('/css/components/prism-line-numbers.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('/css/components/prism-line-highlight.css') }}" type="text/css">
    @parent
@endsection
