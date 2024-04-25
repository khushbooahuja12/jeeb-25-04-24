@extends('layouts.app_v3')
@section('content')

<main id="main">

    <!-- ======= Breadcrumbs ======= -->
    <div class="breadcrumbs">
        <div class="page-header d-flex align-items-center" style="background-image: url('{{ asset('assets_v3/img/media-bg.webp') }}');">
        <div class="container position-relative">
          <div class="row d-flex justify-content-center">
            <div class="col-lg-6 text-center">
              <h2>Media</h2>
              <p>&nbsp;</p>
            </div>
          </div>
        </div>
      </div>
      <nav>
        <div class="container">
          <ol>
            <li><a href="{{ route('home') }}">Home</a></li>
            <li><a href="{{ route('media') }}">Media</a></li>
            <li>What makes Jeeb on Top</li>
          </ol>
        </div>
      </nav>
    </div><!-- End Breadcrumbs -->

    <section class="sample-page">
      <div class="container" data-aos="fade-up">
        
        <div class="section-header">
            {{-- <span>Blog</span> --}}
            <h2>What makes Jeeb on Top</h2>
        </div>
        <p><img src="{{ asset('assets_v3/uploads/jeeb-all-in-one.jpg') }}" alt="" class="img-fluid" width="100%"></p>
        <p>
            Our lives have become easier with the introduction of apps designed to assist users with daily tasks. We no longer need to leave our homes to fulfill our daily needs, including buying groceries, charging our electronics, making travel arrangements, and much more. Instead, we only need to use the apps to deliver our goods to our homes or other desired locations, which helps us save time.
            <br/><br/>
            Jeeb is a widely known grocery delivery app in Qatar that offers a wide range of household and other products to users, like groceries, baked goods, personal care items, pharmacy items, meat and eggs, and other snacks too.
            <br/><br/>
            Using Jeeb, you can get your orders delivered to your doorstep as quickly as you need, in about 40 minutes. This is one of the easiest grocery delivery apps in Qatar, which was crafted with safety in mind during the peak of the COVID pandemic.  Jeeb ensures quick, simple, and hassle-free shopping.
            <br/><br/>
            <h3>Easy Login</h3>
            App users hate the time-consuming steps required to log in and make orders. Jeeb has an easy and user-friendly registration process. It also holds a two-step buying pattern, so customers can order groceries faster and smarter.
            <br/><br/>
            <h3>Smart search methods</h3>
            <br/>
            <img src="{{ asset('assets_v3/uploads/jeeb-qatar-feature-2.jpg') }}" alt="" class="img-fluid" style="display: block; margin: 0px auto; width: 100%; max-width: 600px;">
            <br/><br/>
            Finding what you're looking for in a physical grocery store can be a nightmare. However, using the search and filter features in grocery apps is very simple and practical. Today, most grocery delivery apps implement innovations in grocery search methods that help customers purchase items in minutes. Jeeb also provides searching via voice commands or even by uploading pictures of groceries. This both attracts and pleases users. This exclusive feature makes this app smooth and easy to use for all ages of users.
            <br/><br/>
            <h3>Recommended items</h3>
            The advantage of e-commerce is that certain products can be listed as recommended on the app, such as "featured,"  "related products," etc. Additionally, app users benefit from it. This feature may have been available on some of the grocery shopping apps. Here at Jeeb, the recommended list is shown visually in the app, so users can just click and buy groceries without hassles.
            <br/><br/>
            <h3>Product Suggestions</h3>
            As a growing grocery delivery app, Jeeb welcomes product suggestions from users who are willing to express them. Jeeb will look into suggestions as soon as possible, so customers can buy suggested products in the future. It demonstrates how user-friendly Jeeb is. 
            <br/><br/>
            <h3>Easy Payment</h3>
            Jeeb ensures that they provide secure payment methods so users can make payments smartly. Today, we can see that all grocery delivery apps provide online payment features. Jeeb loves online payments too. It’s all for user convenience only. In this modern era, most customers are willing to pay online because it is completely safe for both ends.
            <br/><br/>
            <h3>Why e-commerce grocery apps are popular?</h3>
            <br/>
            <img src="{{ asset('assets_v3/uploads/jeeb-delivery.jpg') }}" alt="" class="img-fluid" style="display: block; margin: 0px auto; width: 100%; max-width: 600px;">
            <br/><br/>
            <ul>
                <li>Customers do not need to travel to and from grocery stores to start. Through their grocery apps, they can order anything they want, saving a lot of time and money on travel.</li>
                <li>Second, by using search and filter options, they can quickly locate anything they're looking for. They can check their impulsive buying without having to navigate many aisles of supermarkets, are spared from visual stimuli, and do not have to navigate many aisles. More time and money are saved in the end.</li>
                <li>Remember that you are not required to do any physical lifting, which is a huge relief for many people, especially our senior citizens.</li>
                <li>Common people like to visit nearby grocery stores regularly. They also like to go through various products and select what they need. The most irritating part comes at the end. Joining a long line at the bill counter and waiting for their turn to pay the bill doesn’t go well with most of the customers. People love to buy smarter and pay smarter, so online grocery delivery apps are growing in popularity.</li>
                <li>Finally, all shopping apps, including grocery apps, frequently run deals and promotions around regional holidays and seasons, which help users save money with offer.</li>                    
            </ul>
            These are the factors that have contributed to the success of grocery apps like Jeeb.
            <br/><br/>
            <h3>Wrapping app</h3>
            Grocery apps are getting reorganized all around the globe because it makes life a little bit easier than before. Jeeb is a gifted app for people living in Qatar that helps people buy groceries and other food products safely and securely. Eco-friendly packaging and delivery make this app more special. It will take a special place in the hearts of people around Qatar because of its quick and quality delivery. In the end, Jeeb is known for its quality and features, which is what it's all about.
            <br/><br/>
            App link: <a href="https://play.google.com/store/apps/details?id=com.jeeb.user&hl=en" target="_blank">Google Play</a> & <a href="https://apps.apple.com/us/app/jeeb-grocery-service/id1614152924" target="_blank">App Store</a>
            <br/><br/>
        </p>
        <hr/>
        <p>
            <a href="{{ route('media') }}" class="readmore"><i class="bi bi-arrow-left"></i> <span>Back to Media</span></a>
        </p>
      </div>
    </section>

  </main><!-- End #main -->

@endsection