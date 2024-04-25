@extends('layouts.app')
@section('content')
<div id="wrapper">
    <!-- content-holder  -->
    <div class="content-holder scroll-content" data-pagetitle="Parallax Image">
        <div class="nav-holder-dec color-bg"></div>
        <div class="nav-overlay"></div>
        <!-- nav-holder end -->
        <!-- hero-wrap-->
        <div class="hero-wrap fl-wrap full-height scroll-con-sec hidden-section" id="sec1" data-scrollax-parent="true">
            <div class="bg par-elem banner_background" data-bg="{{asset('home_assets/uploads/2019/10/12-Recovered_new.png')}}" data-scrollax="properties: { translateY: '30%' }"></div>
            <!-- <div class="overlay"></div> -->
            <div class="half-hero-wrap">
                <div class="pr-bg"></div>
                <div class="rotate_text hero-decor-let">
                    <div>Download the App</div>
                    <div>Add the Products</div>
                    <div>Select the Time &amp; Checkout</div>
                </div>
                <h1 style="margin-top: -27px;padding-bottom: 6px;"><span><img src="{{asset('home_assets/uploads/2019/10/jeeb_logo.png')}}" class="banner_logo" alt="Jeeb logo"></span><br> Groceries, <span>Groceries</span><br> <span> and </span> Groceries</h1>
                <h4 class="banner_details">Founded in the midst of the COVID-19 pandemic, Jeeb aims to tackle the barriers and complications between day-to-day average grocery consumers, and their needs.</h4>
                <div class="clearfix"></div>
            </div>
            <!-- hero  elements  -->
            <div class="hero-border hb-top"></div>
            <div class="hero-border hb-bottom"></div>
            <div class="hero-border hb-right"></div>
            <div class="hero-corner hiddec-anim"></div>
            <div class="hero-corner2 hiddec-anim"></div>
            <div class="hero-corner3 hiddec-anim"></div>
            <div class="scroll-down-wrap sdw_hero hiddec-anim">
                <div class="mousey">
                    <div class="scroller"></div>
                </div>
                <span>Scroll down to discover</span>
            </div>
            <!-- hero  elements end-->
        </div>
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
                            <li><a class="scroll-link fbgs" href="#sec0" data-bgscr="{{asset('home_assets/uploads/2019/10/about.png')}}" data-bgtex="About"><span>About</span></a></li>
                            <li><a class="scroll-link fbgs" href="#sec2" data-bgscr="{{asset('home_assets/uploads/2019/10/vission.png')}}" data-bgtex="Vision"><span>Vision</span></a></li>
                            <li><a class="scroll-link fbgs" href="#sec7" data-bgscr="{{asset('home_assets/uploads/2019/10/how_we_work.png')}}" data-bgtex="How<br>we operate"><span>How we operate</span></a></li>
                            <li><a class="scroll-link fbgs" href="#secNews" data-bgscr="{{asset('home_assets/uploads/2019/10/how_we_work.png')}}" data-bgtex="News<br>"><span>News</span></a></li>
                        </ul>
                    </nav>
                    <div class="arrowpagenav"></div>
                </div>
                <!--page-scroll-nav end-->
                <div class="clearfix"></div>
                <div class="section-separator bot-element"><span class="fl-wrap"></span></div>
                <div class="clear"></div>
                <section id="sec0" class="hidden-section scroll-con-sec vc-section bot-element" style=" ">
                    <div class="container">
                        <div class="section-title fl-wrap">
                            <h3> ABOUT US::</h3>
                        </div>
                        <div class="row">
                            <div class="wpb_column vc_column_container vc_col-sm-5">
                                <div class="vc_column-inner">
                                    <div class="wpb_wrapper">
                                        <div class="dec-img  fl-wrap"><img src="{{asset('home_assets/uploads/2019/10/phone_swp.png')}}" class="respimg" alt="about"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="wpb_column vc_column_container vc_col-sm-7">
                                <div class="vc_column-inner">
                                    <div class="wpb_wrapper">
                                        <div class="main-about fl-wrap">
                                            <!-- <h5>Curabitur convallis fringilla diam</h5> -->
                                            <h2>Aim <span> of </span>Jeeb</h2>
                                            <!-- <h2>How<br>to boost <span> your creative </span> projects</h2> -->
                                            <div class="main-about-text-area">
                                                <p>Founded in the midst of the COVID-19 pandemic, Jeeb aims to tackle the barriers and complications between day-to-day average grocery consumers, and their needs. This is done through Jeeb's Incentive RPS, strategic logistical management and it's elementary app technology.</p>
                                            </div>
                                            <a href="#" class="btn ajax  fl-btn color-bg" target="_self">Our App</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="sec-number">01.</div>
                    </div>
                    <div class="col-wc_dec col-wc_dec2 col-wc_dec3"></div>
                </section>
                <section id="secNews" class="hidden-section scroll-con-sec vc-section bot-element" style=" ">
                    <div class="col-wc_dec"></div>
                    <div class="container">
                        <div class="section-title fl-wrap">
                            <h3> NEWS::</h3>
                        </div>
                        @include('news_section')
                        <div class="sec-number">02.</div>
                    </div>
                </section>
                <div class="clearfix"></div>
                <section class="dark-bg bot-element counter_section" style="background: #5481c4;">
                    <div class="container">
                        <div class="row">
                            <div class="wpb_column vc_column_container vc_col-sm-12">
                                <div class="vc_column-inner">
                                    <div class="wpb_wrapper">
                                        <div class="inline-facts-container fl-wrap optional_counter">
                                            <div class="inline-facts-wrap">
                                                <div class="inline-facts">
                                                    <div class="milestone-counter">
                                                        <div class="stats animaper">
                                                            <div class="num" data-content="0" data-num="145">0</div>
                                                        </div>
                                                    </div>
                                                    <h6 style="color:white;">Finished orders</h6>
                                                </div>
                                            </div>
                                            <div class="inline-facts-wrap">
                                                <div class="inline-facts">
                                                    <div class="milestone-counter">
                                                        <div class="stats animaper">
                                                            <div class="num" data-content="0" data-num="357">0</div>
                                                        </div>
                                                    </div>
                                                    <h6 style="color:white;">Happy customers</h6>
                                                </div>
                                            </div>
                                            <div class="inline-facts-wrap">
                                                <div class="inline-facts">
                                                    <div class="milestone-counter">
                                                        <div class="stats animaper">
                                                            <div class="num" data-content="0" data-num="825">0</div>
                                                        </div>
                                                    </div>
                                                    <h6 style="color:white;">Working hours</h6>
                                                </div>
                                            </div>
                                            <div class="inline-facts-wrap">
                                                <div class="inline-facts">
                                                    <div class="milestone-counter">
                                                        <div class="stats animaper">
                                                            <div class="num" data-content="0" data-num="15">0</div>
                                                        </div>
                                                    </div>
                                                    <h6 style="color:white;">Awards won</h6>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                <div class="clear"></div>
                <br>
                <div class="section-separator bot-element"><span class="fl-wrap"></span></div>
                <br>
                <div class="clear"></div>
                <section id="sec2" class="hidden-section scroll-con-sec vc-section bot-element" style=" ">
                    <div class="col-wc_dec"></div>
                    <div class="container">
                        <div class="section-title fl-wrap">
                            <h3> VISION::</h3>
                        </div>
                        <div class="row">
                            <div class="wpb_column vc_column_container vc_col-sm-7">
                                <div class="vc_column-inner">
                                    <div class="wpb_wrapper">
                                        <div class="clearfix"></div>
                                        <p>Greetings!, I'm BJ, the founder and CEO of Jeeb. For those who know me personally, they’d agree that if there was ever a true and clear reflection of my personality, it would be Jeeb.For as long as I could remember, specialisation was fundamental to me, hence groceries - but not really. The primary reason we have designated groceries over any other sector is simply due to the current market gap in online grocery shopping in the middle east and specifically Doha, which happens to be our HQ! Specialisation, innovative and user-friendly app technology, incentive rewards and customer-oriented service is pretty much what differentiates Jeeb from the rest, not to mention our RPS.</p>
                                        <p>We thrive to excel and accelerate our services not only in Doha, but all over the Middle East, perhaps even the rest of the world one day.</p>
                                        <p>Automation is increasing, and efficiency is of the essence. It is is inevitable that convenience will prevail over the traditional in-store shopping, and Jeeb sure hopes to be there!</p>
                                        <p><b>Groceries, groceries and groceries!</b></p>
                                        <p><b>Sending you the best,</b></p>
                                        <p><b>BJ</b></p>
                                    </div>
                                </div>
                            </div>
                            <div class="wpb_column vc_column_container vc_col-sm-5">
                                <div class="vc_column-inner">
                                    <div class="wpb_wrapper">
                                        <!-- ;'\
                                  ' -->
                                        <div class="clearfix"></div>
                                        <div class="piechart-holder animaper" data-skcolor="#F68338">
                                            <div class="wpb_wrapper">
                                                <div class="dec-img  fl-wrap"><img src="{{asset('home_assets/uploads/2019/10/jeep_s.png')}}" class="respimg" alt="about"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="sec-number">03.</div>
                    </div>
                </section>
                <div class="clear"></div>
                <section class="video_section dark-bg bot-element" style="background: #5481c4;">
                    <div class="container">
                        <div class="row">
                            <div class="wpb_column vc_column_container vc_col-sm-12">
                                <div class="vc_column-inner">
                                    <div class="wpb_wrapper">
                                        <div class="vc_row wpb_row vc_inner vc_row-fluid">
                                            <div class="wpb_column vc_column_container vc_col-sm-6">
                                                <div class="vc_column-inner">
                                                    <div class="wpb_wrapper">
                                                        <div class="video-box dec-img fl-wrap video-box-custom">
                                                            <iframe style="width:100%;" width="560" height="315" src="https://www.youtube.com/embed/Mxesac55Puo" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                                                            <!-- <img src="./uploads/2019/10/1.jpg" class="respimg" alt="1"><a class="video-box-btn image-popup" href="https://www.youtube.com/watch?v=Mxesac55Puo"><i class="fas fa-play" aria-hidden="true"></i></a> -->
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="wpb_column vc_column_container vc_col-sm-6">
                                                <div class="vc_column-inner">
                                                    <div class="wpb_wrapper">
                                                        <div class="video-promo-text fl-wrap mar-top">
                                                            <h3>JEEB VIDEO PRESENTATION</h3>
                                                            <div class="main-about-text-area">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas in pulvinar neque. Nulla finibus lobortis pulvinar. Donec a consectetur nulla. Nulla posuere sapien vitae lectus suscipit, et pulvinar nisi tincidunt. Aliquam erat volutpat</div>
                                                            <a href="#" class="btn video_btn noajax  fl-btn color-bg" target="_blank">My Youtube Chanel</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="hero-corner"></div>
                </section>
                <div class="clearfix"></div>
                <div class="clear"></div>
                <section id="sec7" class=" vc-section bot-element" >
                    <div class="container">
                        <div class="section-title fl-wrap">
                            <h3> How We Operate</h3>
                        </div>
                        <div class="row">
                            <div class="wpb_column vc_column_container vc_col-sm-4">
                                <div class="vc_column-inner">
                                    <div class="wpb_wrapper">
                                        <div class="process-wrap fl-wrap">
                                            <ul>
                                                <li>
                                                    <h4 style="float: none;padding-left:15px; text-align: center;color: #5481c4;">Download &amp; get<br> the Jeeb App</h4>
                                                    <div class="process-details">
                                                        <div class=""><img src="{{asset('home_assets/uploads/2019/10/download-app-7.gif')}}" class="respimg" alt="about"></div>                                                                    
                                                    </div>
                                                    <span class="process-numder">01.</span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="wpb_column vc_column_container vc_col-sm-4">
                                <div class="vc_column-inner">
                                    <div class="wpb_wrapper">
                                        <div class="process-wrap fl-wrap">
                                            <ul>
                                                <li>                                                               
                                                    <h4 style="float: none;padding-left:15px; text-align: center;color: #5481c4;">Add the product<br> to the Cart</h4>
                                                    <div class="process-details">
                                                        <div class=""><img src="{{asset('home_assets/uploads/2019/10/add-products-7.gif')}}" class="respimg" alt="about"></div>
                                                    </div>
                                                    <span class="process-numder">02.</span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="wpb_column vc_column_container vc_col-sm-4">
                                <div class="vc_column-inner">
                                    <div class="wpb_wrapper">
                                        <div class="process-wrap fl-wrap">
                                            <ul>
                                                <li>
                                                    <h4 style="float: none;padding-left:15px; text-align: center;color: #5481c4;">Select time slot &amp;<br> Checkout</h4>
                                                    <div class="process-details">
                                                        <div class=""><img src="{{asset('home_assets/uploads/2019/10/Select-time-slot-v3.gif')}}" class="respimg" alt="about"></div>
                                                    </div>
                                                    <span class="process-numder">03.</span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="wpb_column vc_column_container vc_col-sm-4">
                                <div class="vc_column-inner">
                                    <div class="wpb_wrapper">
                                        <div class="process-wrap fl-wrap">
                                            <ul>
                                                <li>
                                                    <h4 style="float: none;padding-left:15px; text-align: center;color: #5481c4;" color: #5481c4;>Select time slot &amp;<br> Checkout</h4>
                                                    <div class="process-details">
                                                        <div class=""><img src="{{asset('home_assets/uploads/2019/10/Guy_1.gif')}}" class="respimg" alt="about"></div>
                                                    </div>
                                                    <span class="process-numder">04.</span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="sec-number">04.</div>
                    </div>
                </section>
                <div class="limit-box fl-wrap"></div>
            </div>
            <!--content  end -->
        </div>
        <!--footer-->
        <div class="height-emulator fl-wrap"></div>
        <footer class="main-footer fixed-footer">
            <div class="container">
                <div class="footer-inner fl-wrap">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="footer-box fl-wrap">
                                <div id="nastik_twitter_feed_widget-2" class="footer-widget footer-box fl-wrap widget_nastik_twitter_feed_widget">
                                    <div class="footer-title fl-wrap" style="font-size: 20px;">
                                        <!-- <span>001.</span> -->
                                        Get the <span>App</span>
                                    </div>
                                    <div class="footer-box-item fl-wrap">
                                        <!-- <h1>Get the App</h1> -->
                                        <div class="row">
                                            <div class="col-6 col-sm-6 col-md-6 col-lg-6 footer_play_store_button" style="padding: 30px;">
                                                <a href="#">
                                                    <div class="video-box footer_store_img dec-img fl-wrap"><img class="respimg" src="{{asset('home_assets/uploads/2019/10/google-play-logo2.png')}}" alt="Play Store"></div>
                                                </a>
                                            </div>
                                            <div class="col-6 col-sm-6 col-md-6 col-lg-6 footer_app_store_button" style="padding: 30px;">
                                                <a href="#">
                                                    <div class="video-box footer_store_img dec-img fl-wrap"><img class="respimg" src="{{asset('home_assets/uploads/2019/10/app-store2.png')}}" alt="App Store"></div>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="footer-box fl-wrap">
                                <div id="nastik_contact_widget-2" class="footer-widget footer-box fl-wrap widget_nastik_contact_widget">
                                    <div class="footer-title fl-wrap" style="font-size: 20px;">
                                        Contact <span>Us</span>
                                    </div>
                                    <div class="footer-box-item fl-wrap">
                                        <div class="footer-contacts fl-wrap">
                                            <ul>
                                                <li><i class="fal fa-phone"></i><span>PHONE:</span><a href="tel:+97431666435">+974 31666435</a></li>
                                                <li><i class="fal fa-envelope"></i><span>EMAIL:</span><a href="mailto:admin@jeeb.tech">admin@jeeb.tech</a></li>
                                            </ul>
                                        </div>
                                        <div class="footer-social">
                                            <ul>
                                                <li><a target="_blank" href="{{$social_links['facebook']}}"><i class="fab fa-facebook-f"></i></a></li>
                                                <li><a target="_blank" href="{{$social_links['linkedin']}}"><i class="fab fa-linkedin"></i></a></li>
                                                <li><a target="_blank" href="{{$social_links['instagram']}}"><i class="fab fa-instagram"></i></a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="subfooter fl-wrap">
                    <!-- policy-box-->
                    <div class="policy-box">
                        © JEEB 2021 / All rights reserved.
                    </div>
                    <!-- policy-box end-->
                    <div class="to-top to-top-btn color-bg"><span>To top</span></div>
                </div>
            </div>
            <div class="sec-lines"></div>
            <div class="footer-canvas">
                <div class="dots gallery__dots" data-dots=""></div>
            </div>
        </footer>
        <!--footer  end -->
    </div>
    <!-- content-holder end -->
</div>
@endsection