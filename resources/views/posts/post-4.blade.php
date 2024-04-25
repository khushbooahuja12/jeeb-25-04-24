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
            <li>Why moms love Jeeb</li>
          </ol>
        </div>
      </nav>
    </div><!-- End Breadcrumbs -->

    <section class="sample-page">
      <div class="container" data-aos="fade-up">
        
        <div class="section-header">
            {{-- <span>Blog</span> --}}
            <h2>Why moms love Jeeb</h2>
        </div>
        <p><img src="{{ asset('assets_v3/uploads/jeeb-qatar.jpeg') }}" alt="" class="img-fluid" width="100%"></p>
        <p>
            Grocery shopping is the primary job of moms in every home. In the past, most families headed out to grocery stores to buy things for the entire month or week. As a mother, one of your responsibilities is to ensure that the groceries are fully stacked. In this technologically evolved era, no mom wants to leave home to buy any product from a grocery store because the online shopping culture has changed them to expect to receive any item at their doorsteps.
            <br/><br/>
            Moms of all ages enjoy using online grocery shopping apps that help them get high-quality groceries with offers and discounts. Grocery delivery apps have become the most important thing on everyone's smartphones because no mother wants to spend extra money on groceries. According to a study and report from a reliable source, more than 80% of women would like to use food and grocery delivery apps.
            <br/><br/>
            <h3>Why is it important to have grocery delivery apps on the phone?</h3>
            Today, we all depend on mobile phones for most of our activities. And yes, we can do grocery shopping as well. The curiosity of people about online shopping drives women and men to the court of online grocery delivery apps. Let’s get into the valid reasons why people choose online grocery delivery apps over physical stores.
            <br/><br/>
            <img src="{{ asset('assets_v3/uploads/jeeb-groceries-qatar.jpg') }}" alt="" class="img-fluid" style="display: block; margin: 0px auto; width: 100%; max-width: 600px;">
            <br/><br/>
            <ul>
                <li>Save time and bring flexibility.</li>
                <li>The entire ordering process.</li>
                <li>Easy payment options and saving money</li>
                <li>Save time and bring flexibility.</li>                
            </ul>
            With mobile grocery apps, both women and men can order groceries with just a few clicks while sitting in their comfortable chairs. When it comes to shopping online, one of the key benefits is that there is no need to stand in a long line for bill payment. They can avoid these time-consuming and hectic tasks when using mobile apps for grocery shopping.
            <br/><br/>
            <strong>Streamline the entire online process</strong>
            <br/><br/>
            By providing all the data about the grocery online, it is easier to place orders for women. Most grocery delivery apps are well-known for their on-time delivery of fresh and quality groceries. Mobile grocery apps record every step of a grocery order, so there is no chance of getting cheated. This is completely safe to buy groceries online. 
            <br/><br/>
            <strong>Easy Payment and saving money</strong>
            <br/><br/>
            Similar to eCommerce apps, online grocery apps also provide flexible and convenient payment options to their shoppers. You can pay both online and offline, so people love this method of purchase. Most grocery delivery apps give some special discounts on groceries you buy, so you can save money as well.  
            <br/><br/>
            <h3>How do the groceries apps help mothers?</h3>
            Mother is the kitchen's queen. There is no doubt about this phrase. They are also responsible for purchasing groceries for almost every home in the world. Mothers are hard workers and rarely take breaks. The introduction of grocery delivery apps helps them save both time and effort, so they love it.
            <br/><br/>
            Online purchases of groceries will help them comfortably take some rest, certainly because it delivers any size of groceries to their doorsteps without a hassle. Grocery apps keep them in a safe and secure zone, so they can avoid pandemics and virus attacks. Most grocery delivery apps ensure safe delivery, so mothers do not need to panic about virus risks. Mothers are great money savers too; online grocery delivery apps deliver everything at a reasonable price.
            <br/><br/>
            <h3>What are the best features of the Jeeb App?</h3>
            <br/><br/>
            <img src="{{ asset('assets_v3/uploads/jeeb-qatar-feature.jpg') }}" alt="" class="img-fluid" style="display: block; margin: 0px auto; width: 100%; max-width: 600px;">
            <br/><br/>
            Jeeb, the easiest online grocery shopping apps, ensures hassle-free shopping for people around Qatar. If you have the Jeeb app on your mobile device, you do not need to leave your house for groceries. Let’s see some specific features held by Jeeb.
            <ul>
                <li>Capture Image to cart</li>
                <li>Speech recognition</li>
                <li>Safe and instant delivery</li>
                <li>Safe handling of frozen goods</li>
                <li>Eco-friendly</li>
                <li>Easy ordering</li>
            </ul>
            Jeeb, an online grocery delivery app, has made the shopping experience more interactive and accessible, so anybody can purchase groceries easily through this app. Jeeb also gives you a voice caption feature for adding groceries to your cart. It lets you make your shopping list quickly in two ways: one by clicking on the recommended list, and another by taking a picture and uploading it to your cart. These exciting features make this app more people-centric and user-friendly, so it will find its best place in the hearts of people soon.
            <br/><br/>
            <h3>Wrapping up</h3>
            Online grocery shopping is a new trend that has seen massive growth in the last couple of years. These days, it has been widely implemented in most Indian grocery stores, Chinese supermarkets, and other Asian grocery stores. This is completely changing the way people go grocery shopping, so it’s been welcomed by people all around the globe. Jeeb is now effectively earning the trust of people, which helps each consumer meet their needs for groceries with exciting deals. You can reach Jeeb easily through the below-mentioned methods.
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