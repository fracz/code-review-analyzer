<ul class="list-group result-list" data-project="{{ $project->getAttribute('id') }}">
    @foreach ($results as $result)
        <li class="list-group-item clearfix">
            {!! $analyzer->getContent($result, $project) !!}
        </li>
    @endforeach
</ul>
