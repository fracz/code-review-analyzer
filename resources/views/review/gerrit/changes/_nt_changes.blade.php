<img src="{{ $result['avatar']->url }}" height="{{ $result['avatar']->height }}" class="avatar" />
<span class="list-group-item-value"><strong>{{ $result['name'] }}</strong> ({{ $result['username'] }}): {{ $result['count'] }}</span>
<button type="button" class="btn btn-primary pull-right" data-toggle="modal" data-target="#nt-changes-{{ $result['username'] }}">Zmiany</button>
<div class="modal fade" id="nt-changes-{{ $result['username'] }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Zmiany NT użytkownika {{ $result['name'] }}</h4>
            </div>
            <ul class="list-group">
                @foreach ($result['changes'] as $number => $commit)
                    <li class="list-group-item">
                        <h4 class="list-group-item-heading clearfix">
                            <span class="list-group-item-value">{{ $commit }}</span>
                            <a target="_blank" class="btn btn-default pull-right" href="{{ $project->getChangeUrl($number) }}">Zobacz zmianę</a>
                        </h4>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
