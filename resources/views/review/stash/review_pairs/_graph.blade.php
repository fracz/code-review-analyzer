<script type="text/javascript">
    var cy_review_pairs = {!! json_encode($results) !!};
</script>
<div class="panel-body cy-graph" id="cy-review-pairs"></div>

@section('javascripts')
    <script src="{{ asset('/js/graph.js') }}" type="text/javascript"></script>
    @parent
@endsection
