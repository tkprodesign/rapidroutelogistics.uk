<?php
include('./app.php');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapid Route Logistics | Shipping, Tracking, Freight & Logistics Solutions</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css" />
    
    
    <link rel="stylesheet" href="/assets/stylesheets/main.css?v=<?php echo filemtime($_SERVER['DOCUMENT_ROOT'] . '/assets/stylesheets/main.css'); ?>">
    <link rel="stylesheet" href="/assets/stylesheets/home.css?v=<?php echo filemtime($_SERVER['DOCUMENT_ROOT'] . '/assets/stylesheets/home.css'); ?>">
    <link rel="stylesheet" href="/assets/stylesheets/ts/main.css?v=<?php echo filemtime($_SERVER['DOCUMENT_ROOT'] . '/assets/stylesheets/ts/main.css'); ?>" media="screen and (max-width: 1120px)">
    <link rel="stylesheet" href="/assets/stylesheets/ts/home.css?v=<?php echo filemtime($_SERVER['DOCUMENT_ROOT'] . '/assets/stylesheets/ts/home.css'); ?>" media="screen and (max-width: 1120px)">
    <link rel="stylesheet" href="/assets/stylesheets/ms/main.css?v=<?php echo filemtime($_SERVER['DOCUMENT_ROOT'] . '/assets/stylesheets/ms/main.css'); ?>" media="screen and (max-width: 760px)">
    <link rel="stylesheet" href="/assets/stylesheets/ms/home.css?v=<?php echo filemtime($_SERVER['DOCUMENT_ROOT'] . '/assets/stylesheets/ms/home.css'); ?>" media="screen and (max-width: 760px)">

    <link rel="shortcut icon" href="<?= htmlspecialchars(asset_url('/assets/images/branding/mark-only.png')); ?>" type="image/png">

    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />

    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <script src="https://kit.fontawesome.com/79b279a6c9.js" crossorigin="anonymous"></script>

</head>
<body>
<?php include("common-sections/header.html"); ?>
<section class="hero">
    <!-- DESKTOP CURVE -->
    <div class="custom-shape-divider-bottom-1771138429">
        <svg data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
            <path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V0H0V27.35A600.21,600.21,0,0,0,321.39,56.44Z" class="shape-fill"></path>
        </svg>
    </div>
    <!-- TAB CURVE -->
    <div class="custom-shape-divider-bottom-1771153755">
        <svg data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
            <path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V0H0V27.35A600.21,600.21,0,0,0,321.39,56.44Z" class="shape-fill"></path>
        </svg>
    </div>
    <!-- MOBILE CURVE -->
    <div class="custom-shape-divider-bottom-1771153943">
        <svg data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
            <path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V0H0V27.35A600.21,600.21,0,0,0,321.39,56.44Z" class="shape-fill"></path>
        </svg>
    </div>
    <div class="swiper">
        <div class="swiper-wrapper">
            <div class="swiper-slide hero-1">
                <div class="dark-bg"></div>
                <div class="container">
                    <div class="heading">
                        <!-- <p class="pre-heading">Courier & Logistics Solution</p> -->
                        <h1 class="main-heading">Rapid Routes. Trusted Delivery. Built to <span class="accent">Move Forward.</span> </h1>
                        <p class="sub-heading">Rapid Route Logistics delivers secure, fast, and reliable transport for businesses, families, and time-critical operations with clear tracking and hands-on support from pickup to final mile.</p>
                    </div>
                    <form class="c-t-a" action="/track/" method="get">
                        <div class="input-box">
                            <input type="text" name="id" placeholder="Tracking Number">
                        </div>
                        <button type="submit" class="pri">Track <span class="material-symbols-outlined">chevron_right</span></button>
                    </form>
                </div>
            </div>
            <div class="swiper-slide hero-2">
                <div class="dark-bg"></div>
                <div class="container">
                    <div class="heading">
                        <h1 class="main-heading">Engineered for Precision. <span class="accent">Ready for Every Route.</span></h1>
                        <p class="sub-heading">A modern courier and freight team coordinating scheduled routes, urgent dispatches, secure parcels, and mission-critical movements across domestic and international lanes.</p>
                    </div>
                    <form class="c-t-a" action="/track/" method="get">
                        <div class="input-box">
                            <input type="text" name="id" placeholder="Tracking Number">
                        </div>
                        <button type="submit" class="pri">Track<span class="material-symbols-outlined">chevron_right</span></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>





<section class="ups-branch-context brand-context">
    <div class="container">
        <div class="ups-branch-card">
            <div class="content">
                <p class="eyebrow">Updated Rapid Route Logistics Brand</p>
                <h2>Built Around Speed, Visibility, and Reliable Route Control</h2>
                <p>
                    Rapid Route Logistics combines responsive dispatch, careful handling, and proactive shipment updates to keep every delivery moving with confidence.
                </p>
                <p>
                    Our refreshed identity reflects the way we work: sharp planning, forward momentum, and dependable support for customers who need logistics to stay simple.
                </p>
                <ul>
                    <li>Dedicated support for parcels, documents, freight, and specialty deliveries.</li>
                    <li>Time-definite routing with clear checkpoints from pickup to delivery.</li>
                    <li>Secure handling standards for valuable, sensitive, and urgent shipments.</li>
                </ul>
            </div>
            <div class="visual">
                <img src="<?= htmlspecialchars(asset_url('/assets/images/branding/transparent/icon-alt.png')); ?>" alt="Rapid Route Logistics icon">
            </div>
        </div>
    </div>
</section>




<section class="rrl-stats" id="rrl-stats">
    <div class="stats-container">
        <div class="stat-item">
            <div class="stat-number-row">
                <span class="stat-number" data-count="15000">0</span><span class="stat-suffix">+</span>
            </div>
            <span class="stat-label">Shipments Delivered</span>
        </div>
        <div class="stat-item">
            <div class="stat-number-row">
                <span class="stat-number">99.2</span><span class="stat-suffix">%</span>
            </div>
            <span class="stat-label">On-Time Delivery Rate</span>
        </div>
        <div class="stat-item">
            <div class="stat-number-row">
                <span class="stat-number" data-count="50">0</span><span class="stat-suffix">+</span>
            </div>
            <span class="stat-label">Active Route Destinations</span>
        </div>
        <div class="stat-item">
            <div class="stat-number-row">
                <span class="stat-number">24/7</span>
            </div>
            <span class="stat-label">Support Line</span>
        </div>
    </div>
</section>

<section class="why-choose-us editing">
    <div class="container">
        <div class="heading .heading-1">
            <h2>Precision in Motion. Deliveries You Can Count On</h2>
            <p>From urgent parcels to scheduled freight, Rapid Route Logistics handles every shipment with <b>care, speed, and reliability</b>. Our team turns complex logistics into <b>smooth, dependable solutions</b>, so you can focus on what matters most.</p>
        </div>
        <div class="content">
            <div class="col">
                <h4>Operational Excellence</h4>
                <p>Reliability. Every package is delivered efficiently, safely, and on schedule.</p>
            </div>
            <div class="col">
                <h4>Time-Definite Logistics</h4>
                <p>Punctuality. Every shipment arrives when expected, no exceptions.</p>
            </div>
            <div class="col">
                <h4>Chain of Custody</h4>
                <p>Security. Sensitive documents and valuable parcels are protected throughout every step.</p>
            </div>
            <div class="col">
                <h4>Standardized Reliability</h4>
                <p>Consistency. Every delivery follows a structured process, guaranteeing dependable results.</p>
            </div>
            <div class="col">
                <h4>Network Optimization</h4>
                <p>Precision. Deliveries follow a controlled, efficient process from pickup to drop-off.</p>
            </div>
            <div class="col">
                <h4>Core Operational Mandate</h4>
                <p>Professionalism. Discipline. Dedication. Safety and efficiency define every operation.</p>
            </div>

        </div>
    </div>
</section>





<section class="tools">
    <div class="container">
        <div class="left"></div>
        <div class="right">
            <div class="heading">
                <h2>Tools for Every Step of The Shipping Process</h2>
                <img src="<?= htmlspecialchars(asset_url('/assets/images/home/mc5.jpg')); ?>" alt="shipping tools">
                <p>Explore pricing, send off a batch shipment or change a delivery with our easy-to-use shipping tools.</p>
                <a href="/shipping">See Shipping Tools<span class="material-symbols-outlined">chevron_right</span></a>
            </div>
        </div>
    </div>
</section>





<section class="services-alt">
    <div class="container">
        <div class="heading">
            <h2>Logistics Solutions for Business and Personal Shipping</h2>
            <p>From urgent parcels to critical business documents, Rapid Route Logistics delivers with precision, security, and discipline. Going the extra mile for every customer, every route, every time.</p>
            <div class="toggle">
                <button href="#" class="btn1 active">Business</button>
                <button href="#" class="btn2">Personal</button>
            </div>
        </div>
        <div class="content">
            <!-- Business / Government Services -->
            <div class="g1 active">
                <div class="col">
                    <h3>Business Logistics</h3>
                    <p>Compliance & Security. Time-sensitive and confidential deliveries for companies, teams, and professionals.</p>
                    <a href="/shipping">Start Order <span class="material-symbols-outlined">chevron_right</span></a>
                </div>
                <div class="col">
                    <h3>Bulk & Scheduled Deliveries</h3>
                    <p>Efficiency. Large shipments and recurring deliveries for organizations are executed seamlessly.</p>
                    <a href="/shipping">Book Bulk <span class="material-symbols-outlined">chevron_right</span></a>
                </div>
                <div class="col">
                    <h3>Inter-City & Regional Delivery</h3>
                    <p>Coverage. Shipments reach cities and regions on schedule, supporting daily operations.</p>
                    <a href="/shipping">Ship Route <span class="material-symbols-outlined">chevron_right</span></a>
                </div>
                <div class="col">
                    <h3>Document & Priority Parcel Delivery</h3>
                    <p>Confidentiality. Critical documents and packages are transported securely at every stage.</p>
                    <a href="/shipping">Send Docs <span class="material-symbols-outlined">chevron_right</span></a>
                </div>
            </div>

            <!-- Personal & Family Services -->
            <div class="g2">
                <div class="col">
                    <h3>Same-Day Delivery</h3>
                    <p>Urgency. Critical packages for families and individuals are picked up and delivered the same day.</p>
                    <a href="/shipping">Ship Today <span class="material-symbols-outlined">chevron_right</span></a>
                </div>
                <div class="col">
                    <h3>Family Parcel Delivery</h3>
                    <p>Care. Personal packages and parcels are handled safely, ensuring timely arrival to loved ones.</p>
                    <a href="/shipping">Send Parcel <span class="material-symbols-outlined">chevron_right</span></a>
                </div>
                <div class="col">
                    <h3>Assisted Pickup & Delivery</h3>
                    <p>Convenience. Pickups and deliveries for elderly or mobility-challenged customers are supported efficiently.</p>
                    <a href="/shipping">Book Pickup <span class="material-symbols-outlined">chevron_right</span></a>
                </div>
                <div class="col">
                    <h3>Event & Specialty Deliveries</h3>
                    <p>Flexibility. Packages for personal events, celebrations, or special requests are managed with attention to detail.</p>
                    <a href="/shipping">Ship Event <span class="material-symbols-outlined">chevron_right</span></a>
                </div>
            </div>
        </div>

    </div>
</section>




<section class="banner-1">
    <div class="container">
        <div class="left">
            <h3>Move Faster With Route-Ready Shipping</h3>
            <p>Plan domestic, regional, and international shipments with a support team ready to quote, route, and track your delivery.</p>
            <a href="#">*
                <span class="txt">Terms and Conditions apply</span>
                <span class="material-symbols-outlined icon">open_in_new</span>
            </a>
        </div>
        <div class="right">
            <a href="/shipping?coupon=GoIntL2026">Start Shipping<span class="material-symbols-outlined">chevron_right</span></a>
        </div>
    </div>
</section>




<section class="cards-container">
    <div class="container">
        <div class="heading heading-1-1-1">
            <h2>Rapid Route Services You Can Count On</h2>
            <p>Fast routing, careful handling, and support-led delivery.</p>
        </div>
        <div class="content">
            <div class="col">
                <div class="img-wrapper">
                    <img src="<?= htmlspecialchars(asset_url('/assets/images/home/cd1.jpg')); ?>" alt="Ship and Scale With Rapid Route">
                </div>
                <div class="card-content">
                    <h4>Ship and Scale With Rapid Route</h3>
                    <p>When demand grows, you need a delivery partner that can coordinate parcels, documents, and freight without slowing your operation down.</p>
                    <a target="_blank" href="/services/">
                        <span class="text">Explore Services</span>
                        <span class="material-symbols-outlined icon">open_in_new</span>
                    </a>
                </div>
            </div>
            <div class="col">
                <div class="img-wrapper">
                    <img src="<?= htmlspecialchars(asset_url('/assets/images/home/cd2.jpg')); ?>" alt="Returns, Re-Delivery, and Customer Care">
                </div>
                <div class="card-content">
                    <h4>Returns, Re-Delivery, and Customer Care</h4>
                    <p>Make reverse logistics easier with clear support for returns, re-delivery requests, and shipment issue resolution.</p>
                    <a target="_blank" href="/support/">
                        <span class="text">Get Support</span>
                        <span class="material-symbols-outlined icon">open_in_new</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>




<section class="important-updates">
    <div class="container">
        <div class="heading heading-1-1">
            <h2>Important Updates</h2>
        </div>
        <div class="content">
            <details open>
                <summary>
                    New Rapid Route Logistics Branding
                    <span class="material-symbols-outlined accordion-icon">keyboard_arrow_down</span>
                </summary>
                <div class="inner-content">
                    <p>Our website has been refreshed with the new Rapid Route Logistics identity, colors, logo system, and customer-first messaging.</p>
                </div>
            </details>

            <details>
                <summary>
                    Faster Quote Support
                    <span class="material-symbols-outlined accordion-icon">chevron_right</span>
                </summary>
                <div class="inner-content">
                    <p>Use the quote option or support chat to share pickup, destination, and item details so our team can recommend the right route.</p>
                </div>
            </details>

            <details>
                <summary>
                    Shipment Visibility
                    <span class="material-symbols-outlined accordion-icon">chevron_right</span>
                </summary>
                <div class="inner-content">
                    <p>Keep your tracking number handy to check delivery progress, exception updates, and important shipment milestones.</p>
                </div>
            </details>
        </div>
    </div>
</section>





<?php include("common-sections/footer.html"); ?>





<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js"></script>
<script src="/assets/scripts/home.js?v=<?php echo filemtime($_SERVER['DOCUMENT_ROOT'] . '/assets/scripts/home.js'); ?>"></script>
</body>
</html>
