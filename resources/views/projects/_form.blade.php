@if ($errors->any())
    <div class="alert alert-danger"><strong>Błąd!</strong> Formularz zawiera błędy.</div>
@endif
<div class="form-group @if ($errors->has('label')) has-error has-feedback @endif">
    {!! Form::label('label', 'Nazwa:', ['class' => 'sr-only']) !!}
    {!! Form::text('label', null, ['class' => 'form-control', 'placeholder' => 'Nazwa (tytuł)']) !!}
    @if ($errors->has('label'))
        <span class="glyphicon glyphicon-exclamation-sign form-control-feedback" aria-hidden="true"></span>
        @foreach ($errors->get('label') as $error)
        <span class="help-block">{{ $error }}</span>
        @endforeach
    @endif
</div>
<div class="form-group @if ($errors->has('url')) has-error has-feedback @endif">
    <div class="input-group">
        {!! Form::label('type', 'Url:', ['class' => 'sr-only']) !!}
        <div class="input-group-btn @if ($errors->has('type')) has-error has-feedback @endif">
            {!! Form::label('type', 'Typ:', ['class' => 'sr-only']) !!}
            {!! Form::select('type', $types, null, ['class' => 'selectpicker', 'placeholder' => 'Typ']) !!}
            @if ($errors->has('type'))
                <span class="glyphicon glyphicon-exclamation-sign form-control-feedback" aria-hidden="true"></span>
                @foreach ($errors->get('type') as $error)
                    <span class="help-block">{{ $error }}</span>
                @endforeach
            @endif
        </div>
        {!! Form::text('url', null, ['class' => 'form-control', 'placeholder' => 'Url']) !!}
        @if ($errors->has('url'))
            <span class="glyphicon glyphicon-exclamation-sign form-control-feedback" aria-hidden="true"></span>
            @foreach ($errors->get('url') as $error)
                <span class="help-block">{{ $error }}</span>
            @endforeach
        @endif
    </div>
</div>
<div class="form-group @if ($errors->has('name')) has-error has-feedback @endif">
    {!! Form::label('name', 'Nazwa projektu:', ['class' => 'sr-only']) !!}
    {!! Form::text('name', null, ['class' => 'form-control', 'placeholder' => 'Nazwa projektu']) !!}
    @if ($errors->has('name'))
        <span class="glyphicon glyphicon-exclamation-sign form-control-feedback" aria-hidden="true"></span>
        @foreach ($errors->get('name') as $error)
            <span class="help-block">{{ $error }}</span>
        @endforeach
    @endif
</div>
<div class="form-group repository @if ($errors->has('repository')) has-error has-feedback @endif">
    {!! Form::label('repository', 'Nazwa repozytorium:', ['class' => 'sr-only']) !!}
    {!! Form::text('repository', null, ['class' => 'form-control', 'placeholder' => 'Nazwa repozytorium']) !!}
    @if ($errors->has('repository'))
        <span class="glyphicon glyphicon-exclamation-sign form-control-feedback" aria-hidden="true"></span>
        @foreach ($errors->get('repository') as $error)
            <span class="help-block">{{ $error }}</span>
        @endforeach
    @endif
</div>
<div class="form-group @if ($errors->has('username')) has-error has-feedback @endif">
    {!! Form::label('username', 'Nazwa użytkownika:', ['class' => 'sr-only']) !!}
    {!! Form::text('username', null, ['class' => 'form-control', 'placeholder' => 'Nazwa użytkownika']) !!}
    @if ($errors->has('username'))
        <span class="glyphicon glyphicon-exclamation-sign form-control-feedback" aria-hidden="true"></span>
        @foreach ($errors->get('username') as $error)
            <span class="help-block">{{ $error }}</span>
        @endforeach
    @endif
</div>
<div class="form-group @if ($errors->has('password')) has-error has-feedback @endif">
    {!! Form::label('password', 'Hasło:', ['class' => 'sr-only']) !!}
    {!! Form::password('password', ['class' => 'form-control', 'placeholder' => 'Hasło']) !!}
    @if ($errors->has('password'))
        <span class="glyphicon glyphicon-exclamation-sign form-control-feedback" aria-hidden="true"></span>
        @foreach ($errors->get('password') as $error)
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
