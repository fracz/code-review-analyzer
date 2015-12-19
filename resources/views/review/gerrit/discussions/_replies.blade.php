@if(!empty($messages))
    <ul class="list-group">
        @foreach ($messages as $message)
            <li class="list-group-item">
                <h4 class="list-group-item-heading clearfix">{{ $message['from']['name'] }} ({{ $message['from']['username'] }}) <span class="pull-right">({{ $message['date']->format('d-m-Y H:i') }})</span></h4>
                <p class="list-group-item-text contains-buttons">
                    {{ $message['text'] }}
                    @if (isset($message['file']))
                        <span class="code-buttons">
                            <button class="btn btn-sm btn-default fetch-code" data-file="{{ $message['file'] }}" data-revision="{{ $message['revision'] }}" data-change="{{ $message['change'] }}" data-line="{{ $message['line'] }}">Kod</button>
                            <button class="btn btn-sm btn-default hide-code">Ukryj</button>
                        </span>
                    @endif
                </p>
                @include('review/gerrit/discussions/_replies', ['messages' => $message['replies']])
            </li>
        @endforeach
    </ul>
@endif
