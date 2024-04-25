@if (session()->has('success'))
<div class="alert alert-success fade show alert-dismissible" style="margin-top: 40px;z-index: 99;">
    <a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>
    <strong>{!! session()->get('success') !!}</strong>.
</div>
@endif