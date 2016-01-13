<img src="{{ $result['avatar']->url }}" height="{{ $result['avatar']->height }}" class="avatar" />
<strong>{{ $result['name'] }}</strong> ({{ $result['username'] }}): {{ number_format($result['average'], 2, ',', ' ') }} komentarzy (w {{ $result['commits'] }} zmianach)
