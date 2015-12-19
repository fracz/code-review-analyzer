<span class="list-group-item-value"><strong>{{ $result['count'] }}: {{ $result['subject'] }}</strong> ({{ $result['name'] }})</span>
<button type="button" class="btn btn-primary pull-right" data-toggle="modal" data-target="#hot-topics-{{ $result['id'] }}">Komentarze</button>
<div class="modal fade" id="hot-topics-{{ $result['id'] }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Komentarze dla zmiany {{ $result['subject'] }}</h4>
                <a target="_blank" class="btn btn-sm btn-default pull-right" href="{{ $project->getChangeUrl($result['id']) }}">Zobacz zmianę</a>
            </div>
            @include('review/stash/hot_topics/_replies', ['messages' => $result['messages']])
        </div>
    </div>
</div>
