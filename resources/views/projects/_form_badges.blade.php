@if ($errors->any())
    <div class="alert alert-danger"><strong>Błąd!</strong> Formularz zawiera błędy.</div>
@endif

<div class="form-group @if ($errors->has('badges_period')) has-error has-feedback @endif">
    Ilość dni, z których otrzymuje się odznaki
	{!! Form::input('number', 'badges_period', null, ['class' => 'form-control', 'placeholder' => '7', 'min' => '0', 'step' => '1']) !!}
    @if ($errors->all())
        <span class="glyphicon glyphicon-exclamation-sign form-control-feedback" aria-hidden="true"></span>
        @foreach ($errors->all() as $error)
        <span class="help-block">{{ $error }}</span>
        @endforeach
    @endif
</div>

{!! Form::submit($button, ['class' => 'btn btn-primary pull-right']) !!}

@section("javascripts")
    <script src="{{ asset('/js/project/form.js') }}" type="text/javascript"></script>
@endsection

@section('stylesheets')
    <link rel="stylesheet" href="{{ asset('/css/project/form.css') }}" type="text/css">
@endsection
