@extends('layouts.app_v3')
@section('content')

<main id="main">

    <!-- ======= Breadcrumbs ======= -->
    <div class="breadcrumbs">
      <div class="page-header d-flex align-items-center" style="background-image: url('assets_v3/img/contact-bg.webp');">
        <div class="container position-relative">
          <div class="row d-flex justify-content-center">
            <div class="col-lg-6 text-center">
              <h1>About Us</h1>
              <p>&nbsp;</p>
            </div>
          </div>
        </div>
      </div>
      <nav>
        <div class="container">
          <ol>
            <li><a href="{{ route('home') }}">Home</a></li>
            <li>About Us</li>
          </ol>
        </div>
      </nav>
    </div><!-- End Breadcrumbs -->

    <!-- ======= About Section ======= -->
    <section id="about" class="about">
        <div class="container" data-aos="fade-up">
            <div class="row">
                <div class="col-lg-7">
                    <p>Jeeb is an innovative grocery delivery service that makes grocery shopping fast, easy, and hassle-free. This revolutionary approach was an idea of the Jeeb’s CEO, Mr. Bashar Jaber, to deliver groceries right to the doorsteps. Although there were many food delivery services working in Qatar, none offered grocery delivery. Jeeb’s CEO, Mr. Bashar Jaber, wanted the grocery delivery to be simple, easy and as fast as possible. That’s why he came up with an idea Jeeb calls 2-step delivery. Using Jeeb, you can order all your grocery items at once by just uploading the list, or writing them on the app's built-in notepad, and the logistics department will deliver your item as soon as possible.</p>
                    <p>With Jeeb, you can order everyday items, such as fresh fruits, vegetables, meat, household products, snacks, pet care, cereals, pharmacy, beverages, and more from the store to your doorstep. We're all about convenience and making sure our customers have access to the best groceries delivered quickly and safely. We know that life can be busy and hectic, so let us take the stress out of grocery shopping. Jeeb lets you shop for groceries online without leaving the comfort of your home. Moreover, Jeeb provides delicious recipes, allowing you to spend less time searching for recipes and more time cooking. Get started with Jeeb today, shop for groceries easily and cook mouth watering dishes yourself.</p>
                </div>
                <div class="col-lg-5">
                    <img src="{{ asset('assets_v3/uploads/mr-jabar-bashar.jpg') }}" style="width: 100%; max-width: 400px; display: block;" alt="Mr. Jabar Bashar"/>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <br/><br/>
                    <h2>2 Step</h2>
                    <br/>
                    <img src="{{ asset('assets_v3/uploads/2-STEP-FEATURE.jpg') }}" style="width: 100%; display: block;" alt="Mr. Jabar Bashar"/>
                    <br/><br/>
                    <p>Shopping for groceries has never been easier, thanks to the Jeeb Grocery Delivery
                        App and its 2-step feature! This innovative feature makes it incredibly easy for
                        consumers to order groceries from their mobile devices. All you have to do is
                        upload photos of the items, place your order, and your items will be delivered
                        right to your door. It eliminates the need for a cumbersome in-store experience
                        and allows consumers to conveniently shop for groceries with just a few clicks.
                        With the Jeeb Grocery Delivery App, you can enjoy the convenience of ordering
                        groceries easily. Now that’s something to get excited about!</p>                
                    <br/>
                    <h2>Heavy Duty</h2>
                    <br/>
                    <img src="{{ asset('assets_v3/uploads/HEAVY-DUTY.jpg') }}" style="width: 100%; display: block;" alt="Mr. Jabar Bashar"/>
                    <br/><br/>
                    <p>Jeeb’s logistical capacity and expertise are second to none, meaning we can
                        guarantee your large orders and heavy-duty deliveries are taken care of quickly
                        and efficiently. We understand that the needs of our customers vary, so we make
                        sure our services are tailored to their individual needs. Whether you're stocking
                        up for a party or a large-scale event, we'll make sure you get the products you
                        need in an efficient and timely manner. We understand that running a business
                        can be time-consuming and stressful, so let us take the burden off your shoulders
                        and help you get the products you need to make your business successful. With
                        the Jeeb Grocery Delivery App, you can rest assured that your large orders and
                        heavy-duty deliveries will be taken care of.</p>
                    <br/>
                    <h2>Our Values</h2>
                    <br/>
                    <img src="{{ asset('assets_v3/uploads/OUR-VALUES.jpg') }}" style="width: 100%; display: block;" alt="Mr. Jabar Bashar"/>
                    <br/><br/>
                    <p>At Jeeb, we strive to bring our customers the best grocery delivery experience
                        possible. That's why we have three core values: customer focus, time efficiency,
                        and simplicity. Customer focus is a top priority for us. We aim to make our
                        customers feel valued and to provide them with excellent service every time. We
                        believe our customers should always come first, and that's why we prioritize their
                        satisfaction above all else. Time efficiency is also an important value for us. We
                        know our customers lead busy lives and don't always have time to shop for
                        groceries. That's why we make the process quick and easygoing. We want our
                        customers to be able to get their groceries delivered right away, so they can get on
                        with their day. Finally, we value simplicity and strive to make our app intuitive and
                        user-friendly. We want our customers to be able to order their groceries with just
                        a few taps and without any hassle. Jeeb believes that these values are the key to
                        providing our customers with the best grocery delivery experience.</p>
                    <br/>
                    <h2>How to order from Jeeb?</h2>
                    <br/>
                    <h3>Step 1: Download Jeeb App from Play Store or App Store</h3>
                    <img src="{{ asset('assets_v3/uploads/STEP-1.jpg') }}" style="width: 100%; display: block;" alt="Mr. Jabar Bashar"/>
                    <br/><br/>
                    <h3>Step 2: Sign Up to Jeeb & Choose your location</h3>
                    <img src="{{ asset('assets_v3/uploads/STEP-2.jpg') }}" style="width: 100%; display: block;" alt="Mr. Jabar Bashar"/>
                    <br/><br/>
                    <h3>Step 3: Choose your items manually, upload a picture of your grocery list, or use our voice recognition system, and add them to your cart</h3>
                    <img src="{{ asset('assets_v3/uploads/STEP-3.jpg') }}" style="width: 100%; display: block;" alt="Mr. Jabar Bashar"/>
                    <br/><br/>
                    <h3>Step 4: Select a suitable payment method and make the payment</h3>
                    <img src="{{ asset('assets_v3/uploads/STEP-4.jpg') }}" style="width: 100%; display: block;" alt="Mr. Jabar Bashar"/>
                    <br/><br/>
                    <h3>Step 5: Relax back, your items will be at your doorstep shortly 😃</h3>
                    <img src="{{ asset('assets_v3/uploads/STEP-5.jpg') }}" style="width: 100%; display: block;" alt="Mr. Jabar Bashar"/>
                    <br/><br/>
                </div>
            </div>
        </div>
    </section><!-- End Contact Section -->

</main><!-- End #main -->

@endsection