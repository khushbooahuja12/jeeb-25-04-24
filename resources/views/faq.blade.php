@extends('layouts.app_v3')
@section('content')

<main id="main">

    <!-- ======= Breadcrumbs ======= -->
    <div class="breadcrumbs">
      <div class="page-header d-flex align-items-center" style="background-image: url('assets_v3/img/media-bg.webp');">
        <div class="container position-relative">
          <div class="row d-flex justify-content-center">
            <div class="col-lg-6 text-center">
              <h1>Frequently Asked Questions</h1>
              <p>&nbsp;</p>
            </div>
          </div>
        </div>
      </div>
      <nav>
        <div class="container">
          <ol>
            <li><a href="{{ route('home') }}">Home</a></li>
            <li>Frequently Asked Questions</li>
          </ol>
        </div>
      </nav>
    </div><!-- End Breadcrumbs -->

    <!-- ======= FAQ Section ======= -->
    <section id="service" class="services pt-0 faq">
        <div class="container" data-aos="fade-up">
  
          <div class="section-header">
              <span>Questions about Jeeb</span>
              <h2>Questions about Jeeb</h2>
          </div>
    
          <div class="row justify-content-center" data-aos="fade-up" data-aos-delay="200">
              <div class="col-lg-10">
    
                <div class="accordion accordion-flush">
    
                  <div class="accordion-item">
                    <h3 class="accordion-header">
                      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq-content-14">
                        <i class="bi bi-question-circle question-icon"></i> 
                        What is Jeeb?
                      </button>
                    </h3>
                    <div id="faq-content-14" class="accordion-collapse collapse" data-bs-parent="#faqlist">
                      <div class="accordion-body">
                        Jeeb is an online grocery delivery app that makes grocery purchasing and delivery simple. You can order all your groceries, including ready-to-cook items, fruits and vegetables, pharmacy items, snacks, beverages, dairy, and much more, right to your doorstep. Additionally, Jeeb comes with a variety of recipes, allowing you to cook your favorite dishes in many styles to suit different tastes.
                      </div>
                    </div>
                  </div><!-- # Faq item-->
    
                  <div class="accordion-item">
                    <h3 class="accordion-header">
                      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq-content-15">
                        <i class="bi bi-question-circle question-icon"></i> 
                        Why choose Jeeb?
                      </button>
                    </h3>
                    <div id="faq-content-15" class="accordion-collapse collapse" data-bs-parent="#faqlist">
                      <div class="accordion-body">
                        Imagine that you make a list and get all your grocery items delivered right to your door without ever leaving the comfort of your home. Jeeb does it with its 2-step grocery delivery service! Plus, with Jeeb, you can easily add items to your cart with one click. Also, customers love using Jeeb as it has a user-friendly interface, organized categories, wonderful offers, and amazing customer support.
                      </div>
                    </div>
                  </div><!-- # Faq item-->
    
                  <div class="accordion-item">
                    <h3 class="accordion-header">
                      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq-content-16">
                        <i class="bi bi-question-circle question-icon"></i> 
                        How can I send a message or provide feedback?
                      </button>
                    </h3>
                    <div id="faq-content-16" class="accordion-collapse collapse" data-bs-parent="#faqlist">
                      <div class="accordion-body">
                        If you have a message for Jeeb or want to give feedback on our services and products on Jeeb, feel free to contact us at admin@jeeb.tech. We will make sure you get a quick response.
                      </div>
                    </div>
                  </div><!-- # Faq item-->
    
                  <div class="accordion-item">
                    <h3 class="accordion-header">
                      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq-content-17">
                        <i class="bi bi-question-circle question-icon"></i> 
                        What are the new features of Jeeb?
                      </button>
                    </h3>
                    <div id="faq-content-17" class="accordion-collapse collapse" data-bs-parent="#faqlist">
                      <div class="accordion-body">
                        Jeeb is an innovative grocery delivery app, always working on improving its service. We offer a two-step grocery ordering system. Tap the 2-step icon, add the items you want to purchase to the list, pick up your choices, and Jeeb will deliver it right to your door. Moreover, guess what? You can add the items to the list using the voice recognition method. Isn’t it so easy? Furthermore, Jeeb is a heavy-load grocery delivery service capable of holding and delivering large orders to customers safely and quickly.
                      </div>
                    </div>
                  </div><!-- # Faq item-->
    
                  <div class="accordion-item">
                    <h3 class="accordion-header">
                      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq-content-18">
                        <i class="bi bi-question-circle question-icon"></i> 
                        What is the minimum order for free delivery?
                      </button>
                    </h3>
                    <div id="faq-content-18" class="accordion-collapse collapse" data-bs-parent="#faqlist">
                      <div class="accordion-body">
                        Jeeb offers a free delivery service if your order is over 65 QR. Plus, many products have additional discounts. Keep visiting the offer zone to make sure you benefit from all the interesting offers.
                      </div>
                    </div>
                  </div><!-- # Faq item-->
    
                  <div class="accordion-item">
                    <h3 class="accordion-header">
                      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq-content-19">
                        <i class="bi bi-question-circle question-icon"></i> 
                        What if an item is out of stock?
                      </button>
                    </h3>
                    <div id="faq-content-19" class="accordion-collapse collapse" data-bs-parent="#faqlist">
                      <div class="accordion-body">
                        Our team consistently updates the status of the items. We label or remove the item if it is out of stock. Yet, we try to deliver the items if you place the order before we can label them. In any case, please feel free to contact us, and we will update you shortly.
                      </div>
                    </div>
                  </div><!-- # Faq item-->
    
                  <div class="accordion-item">
                    <h3 class="accordion-header">
                      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq-content-20">
                        <i class="bi bi-question-circle question-icon"></i> 
                        Will Jeeb be responsible for the quality or quantity of the item?
                      </button>
                    </h3>
                    <div id="faq-content-20" class="accordion-collapse collapse" data-bs-parent="#faqlist">
                      <div class="accordion-body">
                        We never compromise on the quality of products from our side. However, if a supplier delivers an incorrect or damaged item, contact the Jeeb support team at admin@jeeb.tech. We will inspect the whole incident and update you as soon as possible.
                      </div>
                    </div>
                  </div><!-- # Faq item-->
    
                  <div class="accordion-item">
                    <h3 class="accordion-header">
                      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq-content-21">
                        <i class="bi bi-question-circle question-icon"></i> 
                        What is your delivery schedule?
                      </button>
                    </h3>
                    <div id="faq-content-21" class="accordion-collapse collapse" data-bs-parent="#faqlist">
                      <div class="accordion-body">
                        We usually deliver items from 8am to 12am (Qatar Standard Time). However, we also work extra hours on special occasions.
                      </div>
                    </div>
                  </div><!-- # Faq item-->
    
                </div>
    
              </div>
            </div>
    
        </div>
      </section><!-- End Services Section -->
  
    <!-- ======= FAQ Section ======= -->
    <section id="service" class="services pt-0 faq">
      <div class="container" data-aos="fade-up">

        <div class="section-header">
            <span>Cancel or Damage</span>
            <h2>Cancel or Damage</h2>
        </div>
  
        <div class="row justify-content-center" data-aos="fade-up" data-aos-delay="200">
            <div class="col-lg-10">
  
              <div class="accordion accordion-flush">
  
                <div class="accordion-item">
                  <h3 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq-content-1">
                      <i class="bi bi-question-circle question-icon"></i> 
                      Why did my order get canceled?
                    </button>
                  </h3>
                  <div id="faq-content-1" class="accordion-collapse collapse" data-bs-parent="#faqlist">
                    <div class="accordion-body">
                      In most cases, orders get canceled because the item might be out of stock or the limits on available quantity. Failure in the payment or connectivity issues with payment options may also cause the cancelation of the order. However, your order might also be canceled if Jeeb suspects you of using a fake identity or location. If any such things happen, contact our customer service.
                    </div>
                  </div>
                </div><!-- # Faq item-->
  
                <div class="accordion-item">
                  <h3 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq-content-2">
                      <i class="bi bi-question-circle question-icon"></i> 
                      What if the delivered item or product is damaged?
                    </button>
                  </h3>
                  <div id="faq-content-2" class="accordion-collapse collapse" data-bs-parent="#faqlist">
                    <div class="accordion-body">
                      Jeeb delivers the items carefully, packing the fragile items in bubble wrap first. But if the product you received is damaged, it might have happened in the delivery process. Jeeb offers a free replacement or a full refund of the damaged product. We recommend taking photos and recording the opening process. Feel free to contact our customer service with proper evidence as soon as possible.
                    </div>
                  </div>
                </div><!-- # Faq item-->
  
                <div class="accordion-item">
                  <h3 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq-content-3">
                      <i class="bi bi-question-circle question-icon"></i> 
                      What if the delivered item or product is incorrect?
                    </button>
                  </h3>
                  <div id="faq-content-3" class="accordion-collapse collapse" data-bs-parent="#faqlist">
                    <div class="accordion-body">
                      If the item or product you received is not the one you ordered, immediately contact Jeeb customer service. You can ask for a replacement, or claim a full refund. However, if you want to keep that item, we will adjust the price and charge or pay you based on the circumstances.
                    </div>
                  </div>
                </div><!-- # Faq item-->
  
                <div class="accordion-item">
                  <h3 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq-content-4">
                      <i class="bi bi-question-circle question-icon"></i> 
                      What if I want to replace an order?
                    </button>
                  </h3>
                  <div id="faq-content-4" class="accordion-collapse collapse" data-bs-parent="#faqlist">
                    <div class="accordion-body">
                      Contact our customer service to replace your order. Kindly include all the details of your order and the reasons. Once we get your message, our support team will contact you right away.
                    </div>
                  </div>
                </div><!-- # Faq item-->
  
                <div class="accordion-item">
                  <h3 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq-content-5">
                      <i class="bi bi-question-circle question-icon"></i> 
                      How can I return my order?
                    </button>
                  </h3>
                  <div id="faq-content-5" class="accordion-collapse collapse" data-bs-parent="#faqlist">
                    <div class="accordion-body">
                      You may return your order by contacting Jeeb’s customer support team. Please include all the product details and reasons to return your order. We will get in touch with you soon after getting your return request.
                    </div>
                  </div>
                </div><!-- # Faq item-->
  
              </div>
  
            </div>
          </div>
  
      </div>
    </section><!-- End Services Section -->

    <!-- ======= FAQ Section ======= -->
    <section id="service" class="services pt-0 faq">
        <div class="container" data-aos="fade-up">
  
          <div class="section-header">
              <span>Referrals, Vouchers, and Offers</span>
              <h2>Referrals, Vouchers, and Offers</h2>
          </div>
    
          <div class="row justify-content-center" data-aos="fade-up" data-aos-delay="200">
              <div class="col-lg-10">
    
                <div class="accordion accordion-flush">
    
                  <div class="accordion-item">
                    <h3 class="accordion-header">
                      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq-content-6">
                        <i class="bi bi-question-circle question-icon"></i> 
                        Why is my voucher inoperative or invalid?
                      </button>
                    </h3>
                    <div id="faq-content-6" class="accordion-collapse collapse" data-bs-parent="#faqlist">
                      <div class="accordion-body">
                        If you get a message that the voucher is invalid, it’s probably because you have entered the code incorrectly or it's expired. Make sure to check the code you entered and its validity. There may also be limitations on which products are eligible for the voucher code. If you experience any further issues, contact Jeeb’s customer service.
                      </div>
                    </div>
                  </div><!-- # Faq item-->
    
                  <div class="accordion-item">
                    <h3 class="accordion-header">
                      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq-content-7">
                        <i class="bi bi-question-circle question-icon"></i> 
                        What is a referral and how would I get points in my wallet?
                      </button>
                    </h3>
                    <div id="faq-content-7" class="accordion-collapse collapse" data-bs-parent="#faqlist">
                      <div class="accordion-body">
                        Refer your friends to get on Jeeb and earn points for free. The points you get in your wallet can be used as an additional discount on the app. For more information, contact Jeeb customer service.
                      </div>
                    </div>
                  </div><!-- # Faq item-->
    
                  <div class="accordion-item">
                    <h3 class="accordion-header">
                      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq-content-8">
                        <i class="bi bi-question-circle question-icon"></i> 
                        How do I get information about offers on Jeeb?
                      </button>
                    </h3>
                    <div id="faq-content-8" class="accordion-collapse collapse" data-bs-parent="#faqlist">
                      <div class="accordion-body">
                        To get up-to-date information on the offers on Jeeb, regularly visit the offer zone page. If you experience a glitch in the feature or the expected offers are not there, please report the issue to the customer support team.
                      </div>
                    </div>
                  </div><!-- # Faq item-->
    
                </div>
    
              </div>
            </div>
    
        </div>
      </section><!-- End Services Section -->
  
    <!-- ======= FAQ Section ======= -->
    <section id="service" class="services pt-0 faq">
        <div class="container" data-aos="fade-up">
  
          <div class="section-header">
              <span>Want to supply Jeeb?</span>
              <h2>Want to supply Jeeb?</h2>
          </div>
    
          <div class="row justify-content-center" data-aos="fade-up" data-aos-delay="200">
              <div class="col-lg-10">
    
                <div class="accordion accordion-flush">
    
                  <div class="accordion-item">
                    <h3 class="accordion-header">
                      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq-content-9">
                        <i class="bi bi-question-circle question-icon"></i> 
                        How do I tie up with Jeeb?
                      </button>
                    </h3>
                    <div id="faq-content-9" class="accordion-collapse collapse" data-bs-parent="#faqlist">
                      <div class="accordion-body">
                        Jeeb has a tie-up policy for interested suppliers. You can reach us via email at admin@jeeb.tech. We will respond to you as soon as possible.
                      </div>
                    </div>
                  </div><!-- # Faq item-->
    
                  <div class="accordion-item">
                    <h3 class="accordion-header">
                      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq-content-10">
                        <i class="bi bi-question-circle question-icon"></i> 
                        Is there any documentation to list my shop with Jeeb?
                      </button>
                    </h3>
                    <div id="faq-content-10" class="accordion-collapse collapse" data-bs-parent="#faqlist">
                      <div class="accordion-body">
                        There are terms and conditions suppliers need to agree with to work with Jeeb. For more information, please email us at admin@jeeb.tech.
                      </div>
                    </div>
                  </div><!-- # Faq item-->
    
                  <div class="accordion-item">
                    <h3 class="accordion-header">
                      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq-content-11">
                        <i class="bi bi-question-circle question-icon"></i> 
                        Do I need to pay for partnering with Jeeb?
                      </button>
                    </h3>
                    <div id="faq-content-11" class="accordion-collapse collapse" data-bs-parent="#faqlist">
                      <div class="accordion-body">
                        Partnering with Jeeb needs to be discussed with the administration first. Please contact us at admin@jeeb.tech.
                      </div>
                    </div>
                  </div><!-- # Faq item-->
    
                  <div class="accordion-item">
                    <h3 class="accordion-header">
                      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq-content-12">
                        <i class="bi bi-question-circle question-icon"></i> 
                        Is there any commission policy?
                      </button>
                    </h3>
                    <div id="faq-content-12" class="accordion-collapse collapse" data-bs-parent="#faqlist">
                      <div class="accordion-body">
                        Jeeb works on the principle of thinking of everybody's best interests. We have certain rules, regulations, commission policies, and documentation. Contact us at admin@jeeb.tech for more information.
                      </div>
                    </div>
                  </div><!-- # Faq item-->
    
                  <div class="accordion-item">
                    <h3 class="accordion-header">
                      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq-content-13">
                        <i class="bi bi-question-circle question-icon"></i> 
                        How do I contact Jeeb for a supplier tie-up?
                      </button>
                    </h3>
                    <div id="faq-content-13" class="accordion-collapse collapse" data-bs-parent="#faqlist">
                      <div class="accordion-body">
                        If you are interested in negotiating a tie-up with Jeeb, feel free to contact us at admin@jeeb.tech. Our administration will check out your proposal and contact you as soon as possible.
                      </div>
                    </div>
                  </div><!-- # Faq item-->
    
                </div>
    
              </div>
            </div>
    
        </div>
      </section><!-- End Services Section -->
  
</main><!-- End #main -->

@endsection