@extends('app')

@section('content')
<div class="col-md-12">
    <h1>Edytuj dane odznak projektu: {{ $project->label }}</h1>
    {!! Form::model($project, ['route' => ['projects.updateBadge', $project->id]]) !!}
        @include ('projects._form_badges', ['button' => 'Zapisz zmiany'])
    {!! Form::close() !!}
    <a class="btn btn-default" href="{{ route('projects') }}">Powrót</a>
</div>
@endsection
