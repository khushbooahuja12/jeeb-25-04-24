{{-- @if (session()->has('error'))
<div class="alert alert-danger fade show alert-dismissible" style="margin-top: 40px;z-index: 99;">
    <a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>
    <strong>{!! session()->get('error') !!}</strong>.
</div>
@endif --}}

@if (session()->has('error'))
<div class="alert alert-danger dark alert-dismissible fade show" role="alert"><strong>{!! session()->get('error') !!}</strong>
    <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
@endif