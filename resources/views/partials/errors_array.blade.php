@if (session()->has('errors'))
<div class="alert alert-danger fade show alert-dismissible" style="margin-top: 40px;z-index: 99;">
    <a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>
    Error ! <br>
    @if (is_array(session()->get('errors')))
        @foreach(session()->get('errors') as $key=>$value)
        <strong>{!! $value !!}</strong><br>
        @endforeach
    @else
        <strong>{!! session()->get('errors') !!}</strong><br>
    @endif
</div>
@endif