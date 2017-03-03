@if(!empty($messages))
    <ul class="list-group">
        @foreach ($messages as $message)
            <li class="list-group-item">
                <h4 class="list-group-item-heading clearfix">{{ $message['from']['name'] }} ({{ $message['from']['username'] }}) <span class="pull-right">({{ $message['date']->format('d-m-Y H:i') }})</span></h4>
                <p class="list-group-item-text">
                    {{ $message['text'] }}
                </p>
                @include('review/stash/discussions/_replies', ['messages' => $message['replies']])
            </li>
        @endforeach
    </ul>
@endif
