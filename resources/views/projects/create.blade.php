@extends('app')

@section('content')
<div class="col-md-12">
    <h1>Dodaj nowy projekt</h1>
    {!! Form::open(['route' => 'projects.store']) !!}
        @include ('projects._form', ['button' => 'Dodaj projekt'])
    {!! Form::close() !!}
</div>
@endsection
