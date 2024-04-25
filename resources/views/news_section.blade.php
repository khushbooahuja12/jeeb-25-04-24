<div id="main-content" class="blog-page">
    <div class="container">
        <div class="row clearfix">
            <div class="col-lg-8 col-md-12 left-box">
                @if($news->count())
                @foreach($news as $key=>$value)
                <div class="card single_post">
                    <div class="body">
                        <div class="img-post">
                            <img class="d-block img-fluid" src="{{$value->getNewsImage?asset('images/news_images').'/'.$value->getNewsImage->file_name:''}}" alt="First slide">
                        </div>
                        <h3>{{$value->title}}</h3><br>
                        <h4 class="">{{$value->description}}</h4>
                    </div>
                </div>
                @endforeach
                @endif
            </div>
            <div class="col-lg-4 col-md-12 right-box">
            </div>
        </div>
        <div class="row"><a href="{{url('/news')}}" style="color: #232323">Click here for more news...</a></div>
    </div>
</div>