@extends('app')

@section('content')
<div class="col-md-12">
    <h1>Projekty</h1>
    <ul class="list-group">
        @foreach ($projects as $project)
        <li class="list-group-item clearfix">
            <h4>
                {{ $project->label }}
                <a class="btn btn-primary pull-right" href="{{ route('projects.show', ['id' => $project->id]) }}">Szczegóły</a>
                <a class="btn btn-default pull-right" href="{{ route('review.analyze', ['id' => $project->id]) }}">Analiza</a>
            </h4>
        </li>
        @endforeach
    </ul>
</div>
@endsection
