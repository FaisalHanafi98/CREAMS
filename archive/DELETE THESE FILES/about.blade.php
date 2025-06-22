<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>About - CREAMS</title>

    <link rel="stylesheet" href="css/fontawesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="{{ asset('assets/about/css/aboutstyle.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/about/css/bootstrap.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/about/css/fontawesome/css/font-awesome.min.css') }}">


</head>
<body>
<section id="ABOUT">
    <div class="about-1">
        <h1> ABOUT US </h1>
        <P>sint Lorem ipsum dolor sit amet, consectetur adipisicing elit. Ducimus optio sectetur minima soluta nulla, alias tenetur doloribus vitae quo eaque, quae illo!fugit tempora ipsum maiores dolorum enim, beatae qui quasisint Lorem ipsum dolor sit amet, consectetur adipisicing elit. Ducimus optio sectetur minima soluta nulla, alias tenetur doloribus vitae quo eaque, quae illo!fugit tempora ipsum maiores dolorum enim, beatae qui quasi</P>
    </div>
    <div id="about-2">
    <div class="content-box-lg">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                   <div class="about-item text-center">
                    <i class="fa fa-book"></i>
                    <h3>MISSION</h3>
                    <hr>
                    <p> Our mission is to empower persons with disabilities to live more independently and achieve their goals by providing accessible and effective rehabilitation services in a community-based setting. Our aim is to improve the quality of life for OKU individuals and remove barriers to accessing rehabilitation services.
            
                    </div>
                </div>
                <div class="col-md-4">
                   <div class="about-item text-center">
                    <i class="fa fa-globe"></i>
                    <h3>VISSION</h3>
                    <hr>
                    <p> Our vision is to create a community-based rehabilitation system that empowers Persons with Disabilities (OKU) to lead fulfilling and independent lives through access to comprehensive, personalized rehabilitation services and support. We aim to break down barriers to access and provide OKU with the tools and resources they need to thrive and reach their full potential.

                    </div>
                </div>
                <div class="col-md-4">
                   <div class="about-item text-center">
                    <i class="fa fa-pencil"></i>
                    <h3>ACHIEVEMENTS</h3>
                    <hr>
                    <p> Our main achievement so far has been the successful development and implementation of our community-based rehabilitation system, which has greatly improved the access and quality of rehabilitation services for Persons with Disabilities in our community. We have received positive feedback from both users and healthcare professionals, and have seen a notable increase in the number of individuals utilizing our services. Additionally, we have been able to effectively manage and utilize our resources, leading to an efficient and cost-effective operation. Moving forward, we hope to continue expanding and improving our services to better meet the needs of our community.
                    </div>
                </div>
                 </div>
            </div>
         </div>
      </div>   
 </section>
 
  <!-- Footer Area Starts -->
<footer class="footer-area">
    <div class="container">
        <div class="row">
            <div class="col-lg-4 col-sm-6">
                <h3>Quick Links</h3>
                <ul class="list-unstyled">
                    <li><a href="{{ url('/admins/dashboard') }}">Home</a></li>
                    <li><a href="{{ route('auth.loginpage') }}">Login</a></li>
                    <li><a href="{{ route('auth.registerpage') }}">Register</a></li>
                </ul>
            </div>
            <div class="col-lg-4 col-sm-6">
                <h3></h3>
                
            </div>
            <div class="col-lg-4 col-sm-6">
                <h3>Contact Us</h3>
                <p>
                    Address: Disability Services Unit (DSU), Office of the Deputy Rector (Trainee Development & Community Engagement), Level 1, Muhammad Abdul-Rauf Building, International Islamic University Malaysia, 53100 Kuala Lumpur.<br>
                    Phone: (+60) 3642 1633 5<br>
                    Email: dsu-creams@iium.edu.my 
                </p>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12 text-center">
                <p class="copyright">Copyright &copy; {{ date('Y') }} CREAMS. All rights reserved.</p>
            </div>
        </div>
    </div>
</footer>
<!-- Footer Area End -->
</body>
</html>

