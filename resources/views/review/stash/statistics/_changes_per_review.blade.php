<img src="{{ $result['avatar'] }}" height="30" class="avatar" />
<strong>{{ $result['name'] }}</strong> ({{ $result['username'] }}): {{ number_format($result['average'], 2, ',', ' ') }} komentarzy (w {{ $result['commits'] }} zmianach)
