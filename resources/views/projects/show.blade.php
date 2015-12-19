@extends('app')

@section('content')
<div class="col-md-12">
    <h1>
        Projekt: {{ $project->label }}
        <a class="btn btn-default pull-right" href="{{ route('projects.edit', ['id' => $project->id]) }}">
            <span class="glyphicon glyphicon-edit"></span> Edytuj
        </a>
        <a class="btn btn-danger pull-right" href="{{ route('projects.delete', ['id' => $project->id]) }}">
            <span class="glyphicon glyphicon-remove"></span> Usuń
        </a>
    </h1>
    <dl class="dl-horizontal">
        <dt>Typ</dt>
        <dd>{{ $project->type }}</dd>
        <dt>Adres</dt>
        <dd>{{ $project->url }}</dd>
        <dt>Nazwa</dt>
        <dd>{{ $project->name }}</dd>
        @if ($project->type == 'stash')
            <dt>Repozytorium</dt>
            <dd>{{ $project->repository }}</dd>
        @endif
        <dt>Użytkownik</dt>
        <dd>{{ $project->username }}</dd>
    </dl>
</div>
@endsection
