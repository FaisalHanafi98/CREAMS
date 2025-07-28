{{-- Enhanced Standard Footer With Icons --}}
<footer class="footer-area">
    <div class="footer-wave-box">
        <div class="footer-wave footer-animation"></div>
    </div>
    
    <div class="container">
        <div class="footer-main">
            <div class="row">
                <div class="col-lg-4 col-md-6">
                    <div class="footer-widget">
                        <div class="footer-logo">
                            <img src="{{ asset('images/logo/logo_ppdk.jpg') }}" alt="CREAMS Logo">
                        </div>
                        <p class="footer-desc">A collaborative initiative dedicated to empowering children with disabilities through community-based rehabilitation services.</p>
                        <div class="social-icons">
                            <a href="#" class="social-icon disabled" data-toggle="tooltip" title="Coming Soon - Our Facebook page is being set up"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" class="social-icon disabled" data-toggle="tooltip" title="Coming Soon - Our Twitter page is being set up"><i class="fab fa-twitter"></i></a>
                            <a href="#" class="social-icon disabled" data-toggle="tooltip" title="Coming Soon - Our Instagram page is being set up"><i class="fab fa-instagram"></i></a>
                            <a href="#" class="social-icon disabled" data-toggle="tooltip" title="Coming Soon - Our LinkedIn page is being set up"><i class="fab fa-linkedin-in"></i></a>
                        </div>
                        <p class="small text-muted mt-2">Social media pages launching soon!</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="footer-widget">
                        <h3>Quick Links</h3>
                        <ul class="footer-links">
                            <li><a href="{{ route('home') }}"><i class="fas fa-home"></i> Home</a></li>
                            <li><a href="{{ route('home') }}#about"><i class="fas fa-info-circle"></i> About</a></li>
                            <li><a href="{{ route('volunteer') }}"><i class="fas fa-hands-helping"></i> Volunteer</a></li>
                            <li><a href="{{ route('contact') }}"><i class="fas fa-envelope"></i> Contact</a></li>
                            <li><a href="{{ route('auth.loginpage') }}"><i class="fas fa-sign-in-alt"></i> Staff Portal</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="footer-widget">
                        <h3><i class="fas fa-paper-plane"></i> Stay Connected</h3>
                        <div class="newsletter-notice p-3 rounded" style="background: #f8f9fa; border: 2px dashed #dee2e6;">
                            <p class="text-muted mb-2"><i class="fas fa-construction"></i> Newsletter subscription is currently under development.</p>
                            <p class="small text-muted">Check back soon to receive updates on volunteer opportunities and impact stories!</p>
                        </div>
                        <div class="contact-info">
                            <p><i class="fas fa-phone-alt"></i> (+60) 3642 1633 5</p>
                            <p><i class="fas fa-envelope"></i> info@creams.org</p>
                            <p><i class="fas fa-map-marker-alt"></i> IIUM, Gombak, Selangor</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="copyright">
        <div class="container">
            <div class="row align-items-centre">
                <div class="col-md-6">
                    <p>&copy; {{ date('Y') }} CREAMS. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-right">
                    <ul class="footer-bottom-links">
                        <li><a href="#"><i class="fas fa-shield-alt"></i> Privacy Policy</a></li>
                        <li><a href="#"><i class="fas fa-gavel"></i> Terms of Service</a></li>
                        <li><a href="#"><i class="fas fa-sitemap"></i> Sitemap</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</footer>

<style>
.social-icon.disabled {
    opacity: 0.5;
    cursor: not-allowed;
    pointer-events: none;
}

.social-icon.disabled:hover {
    transform: none;
    background: initial;
}

.newsletter-notice {
    background: #f8f9fa !important;
    border: 2px dashed #dee2e6 !important;
}
</style>