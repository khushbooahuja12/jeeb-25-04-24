@extends('layouts.app')
@section('content')
<div id="wrapper">
    <!-- content-holder  -->
    <div class="content-holder scroll-content" data-pagetitle="Parallax Image">
        <div class="nav-holder-dec color-bg"></div>
        <div class="nav-overlay"></div>
        <!-- nav-holder end -->
        <!-- hero-wrap-->        
        <!-- fixed-column-wrap -->
        <div class="fixed-column-wrap">
            <div class="progress-bar-wrap">
                <div class="progress-bar color-bg"></div>
            </div>
            <div class="column-image fl-wrap full-height">
                <div class="bg bg-scroll" data-bg=""></div>
                <div class="overlay"></div>
                <div class="column-image-anim"></div>
            </div>
            <div class="fcw-dec"></div>
            <div class="fixed-column-tilte fdct fcw-title"><span id="quote"></span></div>
        </div>
        <!-- fixed-column-wrap end-->
        <!-- hero-wrap end-->
        <!-- column-wrap -->
        <div class="column-wrap">
            <!--content -->
            <div class="content">
                <!--page-scroll-nav-->
                <div class="page-scroll-nav fl-wrap">
                    <nav class="scroll-init color2-bg">
                        <ul class="no-list-style">
                            <li><a class="scroll-link" href="{{url('/#sec0')}}" ><span>About</span></a></li>
                            <li><a class="scroll-link" href="{{url('/#sec2')}}" ><span>Vision</span></a></li>
                            <li><a class="scroll-link" href="{{url('/#sec7')}}" ><span>How we operate</span></a></li>
                            <li><a class="scroll-link fbgs" href="#secNews" data-bgscr="{{asset('home_assets/uploads/2019/10/how_we_work.png')}}" data-bgtex="News<br>"><span>News</span></a></li>
                        </ul>
                    </nav>
                    <div class="arrowpagenav"></div>
                </div>
                <!--page-scroll-nav end-->
                <div class="clearfix"></div>
                <section id="secNews" class=" vc-section bot-element">
                    <div id="main-content" class="blog-page">
                        <div class="container">
                            <div class="row clearfix">
                                <div class="col-lg-12 col-md-12 left-box">
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
                                    </div><br>
                                    @endforeach
                                    @endif
                                </div>                                
                            </div>
                        </div>
                    </div>
                </section>
                <div class="limit-box fl-wrap"></div>
            </div>
            <!--content  end -->
        </div>
        <style>
            .footer_store_img:before {
                left: auto;
                right: -10px;
            }

            .footer_store_img img {
                margin-bottom: -3px;
                margin-right: -6px;
            }
        </style>
        <!--footer-->
        <div class="height-emulator fl-wrap"></div>        
        <!--footer  end -->
    </div>
    <!-- content-holder end -->
</div>
@endsection