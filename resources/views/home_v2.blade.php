@extends('layouts.app_v2')
@section('header-banner')
<img
    src="{{ asset('assets_v2/images/home-banner.webp') }}" class="home-header-banner"
    alt="Jeeb"/>
@endsection
@section('content')
<div id="wrapper">
    <!-- Section 1 -->
    <section class="about">
        <div class="about-img">
            <img
                src="{{ asset('assets_v2/images/hand-with-iphone.webp') }}"
                alt="Hand with iPhone"/>
        </div>
        <div class="about-text">
            <div class="about-text-content">
            Founded in the midst of the COVID-19 pandemic, Jeeb aims to tackle the barriers and 
            complications between day-to-day average grocery consumers, and their needs. 
            This is done through Jeeb's Incentive RPS, strategic logistical management and 
            it's elementary app technology.
            </div>
            <img
                src="{{ asset('assets_v2/images/rectangle-bg.webp') }}"
                alt="" class="about-text-img"/>
        </div>
    </section>
    <!-- !END - Section 1 -->

    <!-- Section 2 -->
    <section class="operation-area">
        <img
            src="{{ asset('assets_v2/images/area-of-operations.webp') }}"
            alt="Area Of Operations" class="operation-area-img"/>
    </section>
    <!-- !END - Section 2 -->

    <!-- Section 3 -->
    <section class="how-we-operate">
        <video autoplay muted loop id="myVideo">
            <source src="{{ asset('assets_v2/videos/how-we-operate.mp4') }}" type="video/mp4">
        </video>
    </section>
    <!-- !END - Section 3 -->
    
</div>
@endsection