@extends('app')

@section('content')
<div class="col-md-12">
    <h1>Analiza: {{ $project->label }}</h1>
    {!! Form::open(['route' => ['review.generate', $project->id]]) !!}
    @if ($errors->any())
        <div class="alert alert-danger"><strong>Błąd!</strong> Formularz zawiera błędy.</div>
    @endif
    <div class="col-md-5">
        <div class="form-group @if ($errors->has('from')) has-error has-feedback @endif">
            {!! Form::label('from', 'Data początkowa:') !!}
            {!! Form::text('from', $from, ['class' => 'form-control']) !!}
            @if ($errors->has('from'))
                <span class="glyphicon glyphicon-exclamation-sign form-control-feedback" aria-hidden="true"></span>
                @foreach ($errors->get('from') as $error)
                    <span class="help-block">{{ $error }}</span>
                @endforeach
            @endif
        </div>
    </div>
    <div class="col-md-5">
        <div class="form-group @if ($errors->has('to')) has-error has-feedback @endif">
            {!! Form::label('to', 'Data końcowa:') !!}
            {!! Form::text('to', $to, ['class' => 'form-control']) !!}
            @if ($errors->has('to'))
                <span class="glyphicon glyphicon-exclamation-sign form-control-feedback" aria-hidden="true"></span>
                @foreach ($errors->get('to') as $error)
                    <span class="help-block">{{ $error }}</span>
                @endforeach
            @endif
        </div>
    </div>
    <div class="col-md-2">
        <label>&nbsp;</label>
        {!! Form::submit('Pobierz dane', ['class' => 'btn btn-primary form-control']) !!}
    </div>
    {!! Form::close() !!}
</div>
@endsection

@section('javascripts')
    <script src="{{ asset('/js/app.js') }}" type="text/javascript"></script>
    <script src="{{ asset('/js/analyze.js') }}" type="text/javascript"></script>
    <script src="{{ asset('/js/configure.js') }}" type="text/javascript"></script>
    @parent
@endsection
