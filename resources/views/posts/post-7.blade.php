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
            <li>What really differentiates Jeeb</li>
          </ol>
        </div>
      </nav>
    </div><!-- End Breadcrumbs -->

    <section class="sample-page">
      <div class="container" data-aos="fade-up">
        
        <div class="section-header">
            {{-- <span>Blog</span> --}}
            <h2>What really differentiates Jeeb</h2>
        </div>
        <p><img src="{{ asset('assets_v3/uploads/what-really-differentiates-jeeb.jpeg') }}" alt="" class="img-fluid" width="100%"></p>
        <p>
            Ordering groceries online has now become a trend. The introduction of the grocery delivery model keeps customers in a safe zone. Online grocery delivery is not a new approach; it has been growing in every corner of the world. It is easier, safer, and faster, so most families enjoy using instant grocery delivery apps. Jeeb, the best grocery delivery app, is known for its easy and quick delivery. In Qatar, people have no time to visit shopping centers because they have realized that Jeeb is the best option for buying groceries.
            <br/><br/>
            <h3>Handy Features of Using Jeeb</h3>
            <br/>
            <ul>
                <li>You can order bulk or minimal groceries anytime.</li>
                <li>The delivery is made to your front door.</li>
                <li>We send the best quality groceries.</li>
                <li>You can stay away from the hassle of crowds and long lines.</li>
                <li>Get the right prices.</li>
                <li>You can stay on budget.</li>
            </ul>
            <br/>
            <img src="{{ asset('assets_v3/uploads/order-from-jeeb-app.jpeg') }}" alt="" class="img-fluid" style="display: block; margin: 0px auto; width: 100%; max-width: 600px;">
            <br/><br/>
            <h3>Ease of use</h3>
            Online grocery shopping is a modern culture that is influenced by people's fast-paced lifestyles. Jeeb is one of the fastest-growing grocery delivery apps ever, located in and around Qatar. By using Jeeb, you can buy fruits, vegetables, ready-to-cook items, snacks, meat and eggs, pharmacy products, and other household products. By clicking on recommended items, you can make your shopping list. All other online grocery apps don't have this feature, but Jeeb does. You can also buy things from Jeeb by taking a picture of your shopping lists. When you have the Jeeb mobile app on your mobile phone, there is no chance of inconvenience while buying groceries.
            <br/><br/>
            <h3>More Variety</h3>
            Jeeb allows you to choose almost any item or brand you are looking for. You can shop for the best-quality groceries and household products without limitations. Jeeb also accepts valuable suggestions from the customer end. There are a variety of brands associated with Jeeb, so you will never complain about the quality.
            <br/><br/>
            <h3>Sharp door delivery</h3>
            Due to various reasons, demand for home delivery is at its peak. Jeeb grocery delivery service will deliver your ordered items to your home at the time you specify. Most senior citizens and busy people in Qatar want instant door delivery of groceries, but it’s only possible with Jeeb. Quick home delivery is a big advantage for people using Jeeb because it never takes too much time to deliver any size of the order.
            <br/><br/>
            <h3>Cut down impulse spending</h3>
            In this expensive world, people are always on a budget when it comes to buying groceries online and offline. While using Jeeb, you can see the total cost as you add groceries to your cart. It also allows you to search and compare brands, so you have an opportunity to buy planned groceries within your planned budget. You have a chance to cut down the package size when it goes beyond your budget, so you can fulfill your shopping without disappointments.
            <br/><br/>
            <h3>Eco-friendly</h3>
            We are living in an uncertain world where people suffer from virus outbreaks and other pollution issues. Jeeb is an eco-friendly grocery delivery service that cares a lot about the environment. It ensures safe delivery with eco-friendly packaging. Recyclable and biodegradable bags have been used to pack your orders. Jeeb implements on-time delivery with eco-friendly bags. Hence, Jeeb is getting a warm welcome from the people of Qatar.
            <br/><br/>
            <h3>How beneficial is online grocery shopping?</h3>
            <br/>
            <img src="{{ asset('assets_v3/uploads/delivered-from-jeeb-app.jpeg') }}" alt="" class="img-fluid" style="display: block; margin: 0px auto; width: 100%; max-width: 600px;">
            <br/><br/>
            Weekly or monthly grocery purchases are an extra headache for people, especially women. When you purchase a lot, you might need the support of human beings to carry things. By providing the option of home delivery, online delivery apps alleviate this type of headache.
            <br/><br/>
            Weekly or monthly grocery purchases are an extra headache for people, especially women. When you purchase a lot, you might need the support of human beings to carry things. By providing the option of home delivery, online delivery apps alleviate this type of headache.
            <br/><br/>
            Buying in bulk is such an economical way to cut your grocery costs. Jeeb can deliver any size of groceries to your home without hassles. Here, you don’t need to carry things too. You just need a grocery delivery app to purchase whole things with a few clicks. Jeeb accepts online payment, so you can finish off your shopping virtually without trouble.
            <br/><br/>
            If you intend to do in-store shopping, you should make an effort to get to the stores and back home. It is a time-consuming and risky option to buy groceries. Jeeb gives you rest from the efforts you take to visit stores. It simply delivers groceries to your home or commercial place as you ordered.
            <br/><br/>
            <h3>Wrapping up</h3>
            In these days, online shopping apps are gaining huge attention from all around the world. You can buy anything smartly and safely through apps. Jeeb is a grocery delivery app that delivers groceries and other necessities smartly, flexibly, and instantly. Complete satisfaction is guaranteed after started using Jeeb for buying groceries.
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