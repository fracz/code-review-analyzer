@extends('app')

@section('content')
<div class="col-md-12">
    <h1>Edytuj projekt: {{ $project->label }}</h1>
    {!! Form::model($project, ['route' => ['projects.update', $project->id]]) !!}
        @include ('projects._form', ['button' => 'Zapisz zmiany'])
    {!! Form::close() !!}
    <a class="btn btn-default" href="{{ route('projects.show', ['id' => $project->id]) }}">Powrót</a>
</div>
@endsection
