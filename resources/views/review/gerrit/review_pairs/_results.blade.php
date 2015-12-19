<h4 class="list-group-item-heading"><strong>{{ $result['name'] }}</strong> ({{ $result['username'] }})</h4>
<ul class="list-unstyled">
    @foreach ($result['pairs'] as $pair)
        <li class="clearfix">
            <span class="list-group-item-value">{{ $pair['name'] }} ({{ $pair['username'] }}): {{ count($pair['commits']) }} zmian</span>
            <button type="button" class="btn btn-primary pull-right" data-toggle="modal" data-target="#review-pairs-{{ $result['username'] }}-{{ $pair['username'] }}">Zmiany</button>
            <div class="modal fade" id="review-pairs-{{ $result['username'] }}-{{ $pair['username'] }}" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Zamknij"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title">Zmiany użytkownika {{ $result['name'] }} sprawdzone przez {{ $pair['name'] }}</h4>
                        </div>
                        <ul class="list-group">
                            @foreach ($pair['commits'] as $number => $commit)
                                <li class="list-group-item clearfix">
                                    <span class="list-group-item-value">{{ $commit['subject'] }}</span>
                                    <ul class="list-unstyled">
                                        @foreach ($commit['revisions'] as $revision)
                                        <li class="clearfix">
                                            <span class="list-group-item-value">Rewizja {{ $revision }}</span>
                                            <a target="_blank" class="btn btn-default pull-right" href="{{ $project->getChangeUrl($number, $revision) }}">Zobacz zmianę</a>
                                        </li>
                                        @endforeach
                                    </ul>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </li>
    @endforeach
</ul>
