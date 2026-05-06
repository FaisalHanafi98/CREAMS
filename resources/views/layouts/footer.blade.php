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
                            <li><a href="{{ url('/') }}"><i class="fas fa-home"></i> Home</a></li>
                            <li><a href="{{ route('contact') }}"><i class="fas fa-envelope"></i> Contact</a></li>
                            <li><a href="{{ route('volunteer') }}"><i class="fas fa-hands-helping"></i> Volunteer</a></li>
                            <li><a href="{{ route('auth.loginpage') }}"><i class="fas fa-sign-in-alt"></i> Staff Portal</a></li>
                        </ul>
                        <h3 class="mt-4">About IIUM</h3>
                        <ul class="footer-links">
                            <li><a href="https://www.iium.edu.my" target="_blank" rel="noopener"><i class="fas fa-university"></i> IIUM Official Website</a></li>
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
                        <li><a href="#" data-toggle="modal" data-target="#privacyPolicyModal"><i class="fas fa-shield-alt"></i> Privacy Policy</a></li>
                        <li><a href="#" data-toggle="modal" data-target="#termsOfServiceModal"><i class="fas fa-gavel"></i> Terms of Service</a></li>
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

{{-- Terms of Service Modal --}}
<div class="modal fade" id="termsOfServiceModal" tabindex="-1" role="dialog" aria-labelledby="termsOfServiceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="termsOfServiceModalLabel">
                    <i class="fas fa-gavel"></i> Terms of Service
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="tos-content">
                    <p class="text-muted mb-4"><strong>Effective Date:</strong> {{ date('F d, Y') }}</p>

                    <h5>1. Introduction</h5>
                    <p>Welcome to IIUM PD-CARE (Community-based Rehabilitation Management System - CREAMS). These Terms of Service ("Terms") govern your access to and use of our platform, services, and facilities. By accessing or using CREAMS, you agree to be bound by these Terms.</p>

                    <h5>2. About IIUM PD-CARE</h5>
                    <p>IIUM PD-CARE is a community-based rehabilitation center operated under the International Islamic University Malaysia (IIUM), providing comprehensive support and services for children with disabilities and their families.</p>

                    <h5>3. User Eligibility</h5>
                    <p>The CREAMS platform is intended for:</p>
                    <ul>
                        <li>Authorized staff members of IIUM PD-CARE</li>
                        <li>Parents/guardians of enrolled trainees</li>
                        <li>Approved volunteers</li>
                        <li>General public (for information and volunteer registration)</li>
                    </ul>

                    <h5>4. User Accounts and Access</h5>
                    <p><strong>4.1 Account Registration:</strong> Staff members and authorized users must register with valid credentials provided by IIUM PD-CARE administration.</p>
                    <p><strong>4.2 Account Security:</strong> Users are responsible for maintaining the confidentiality of their login credentials and for all activities under their account.</p>
                    <p><strong>4.3 Unauthorized Access:</strong> Attempting to access areas of the system without proper authorization is strictly prohibited.</p>

                    <h5>5. Services Provided</h5>
                    <p>CREAMS facilitates:</p>
                    <ul>
                        <li>Trainee registration and profile management</li>
                        <li>Activity scheduling and enrollment</li>
                        <li>Attendance tracking and reporting</li>
                        <li>Communication between staff and guardians</li>
                        <li>Asset and resource management</li>
                        <li>Letter generation and documentation</li>
                        <li>Volunteer coordination</li>
                    </ul>

                    <h5>6. Data Protection and Privacy</h5>
                    <p><strong>6.1 Personal Data:</strong> We collect and process personal information in accordance with Malaysian Personal Data Protection Act (PDPA) 2010.</p>
                    <p><strong>6.2 Sensitive Information:</strong> Information about trainees, including disabilities and health conditions, is treated with utmost confidentiality.</p>
                    <p><strong>6.3 Data Usage:</strong> Personal data is used solely for rehabilitation services, program management, and communication purposes.</p>

                    <h5>7. User Responsibilities</h5>
                    <p>Users must:</p>
                    <ul>
                        <li>Provide accurate and up-to-date information</li>
                        <li>Use the system only for its intended purposes</li>
                        <li>Respect the privacy and confidentiality of others</li>
                        <li>Comply with all applicable laws and regulations</li>
                        <li>Report any security concerns or data breaches immediately</li>
                    </ul>

                    <h5>8. Prohibited Activities</h5>
                    <p>Users must not:</p>
                    <ul>
                        <li>Share login credentials with unauthorized persons</li>
                        <li>Access or attempt to access data beyond their authorization level</li>
                        <li>Upload malicious software or harmful content</li>
                        <li>Misuse or misrepresent information from the system</li>
                        <li>Interfere with the system's operation or security</li>
                    </ul>

                    <h5>9. Trainee Services</h5>
                    <p><strong>9.1 Enrollment:</strong> Trainee enrollment is subject to assessment and approval by IIUM PD-CARE staff.</p>
                    <p><strong>9.2 Service Availability:</strong> Services are provided based on center capacity and trainee needs.</p>
                    <p><strong>9.3 Attendance:</strong> Regular attendance is expected. Extended absences should be communicated to staff.</p>
                    <p><strong>9.4 Safety:</strong> IIUM PD-CARE strives to provide a safe environment. However, parents/guardians acknowledge inherent risks in rehabilitation activities.</p>

                    <h5>10. Volunteer Services</h5>
                    <p><strong>10.1 Application:</strong> Volunteer applications are reviewed and approved by IIUM PD-CARE administration.</p>
                    <p><strong>10.2 Conduct:</strong> Volunteers must adhere to the code of conduct and respect trainee dignity.</p>
                    <p><strong>10.3 Background Checks:</strong> IIUM PD-CARE reserves the right to conduct background checks on volunteers.</p>

                    <h5>11. Intellectual Property</h5>
                    <p>All content, logos, designs, and materials on CREAMS are property of IIUM PD-CARE and IIUM. Unauthorized reproduction or distribution is prohibited.</p>

                    <h5>12. Communications</h5>
                    <p><strong>12.1 Official Communications:</strong> Important notices will be sent via the system's messaging feature or registered email.</p>
                    <p><strong>12.2 Feedback:</strong> Users may provide feedback through the contact form or official channels.</p>

                    <h5>13. Limitation of Liability</h5>
                    <p>IIUM PD-CARE and IIUM shall not be liable for:</p>
                    <ul>
                        <li>System downtime or technical issues beyond our control</li>
                        <li>Loss of data due to user error or unauthorized access</li>
                        <li>Indirect or consequential damages arising from system use</li>
                    </ul>

                    <h5>14. Service Modifications</h5>
                    <p>IIUM PD-CARE reserves the right to:</p>
                    <ul>
                        <li>Modify or discontinue services with reasonable notice</li>
                        <li>Update system features and functionalities</li>
                        <li>Change operating hours or service availability</li>
                    </ul>

                    <h5>15. Termination</h5>
                    <p><strong>15.1 By User:</strong> Users may request account deactivation by contacting administration.</p>
                    <p><strong>15.2 By IIUM PD-CARE:</strong> We reserve the right to suspend or terminate accounts for violation of these Terms or inappropriate conduct.</p>

                    <h5>16. Governing Law</h5>
                    <p>These Terms are governed by the laws of Malaysia. Any disputes shall be resolved in Malaysian courts.</p>

                    <h5>17. Islamic Values</h5>
                    <p>IIUM PD-CARE operates based on Islamic principles of compassion, respect, and dignity. All users are expected to conduct themselves in accordance with these values.</p>

                    <h5>18. Changes to Terms</h5>
                    <p>We may update these Terms periodically. Continued use of CREAMS after changes constitutes acceptance of updated Terms.</p>

                    <h5>19. Contact Information</h5>
                    <p>For questions about these Terms, please contact:</p>
                    <p>
                        <strong>IIUM PD-CARE</strong><br>
                        International Islamic University Malaysia<br>
                        Gombak, Selangor<br>
                        Phone: (+60) 3642 1633 5<br>
                        Email: info@creams.org
                    </p>

                    <div class="alert alert-info mt-4">
                        <i class="fas fa-info-circle"></i> <strong>Note:</strong> By using CREAMS, you acknowledge that you have read, understood, and agree to be bound by these Terms of Service.
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- Privacy Policy Modal --}}
<div class="modal fade" id="privacyPolicyModal" tabindex="-1" role="dialog" aria-labelledby="privacyPolicyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="privacyPolicyModalLabel">
                    <i class="fas fa-shield-alt"></i> Privacy Policy
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="privacy-content">
                    <p class="text-muted mb-4"><strong>Last Updated:</strong> {{ date('F d, Y') }}</p>

                    <h5>1. Introduction</h5>
                    <p>IIUM PD-CARE ("we," "us," or "our") is committed to protecting the privacy and security of your personal information. This Privacy Policy explains how we collect, use, disclose, and safeguard information through our Community-based Rehabilitation Management System (CREAMS).</p>

                    <h5>2. Compliance</h5>
                    <p>This policy complies with the Malaysian Personal Data Protection Act (PDPA) 2010 and follows Islamic principles of trust (Amanah) and confidentiality.</p>

                    <h5>3. Information We Collect</h5>
                    <p><strong>3.1 Trainee Information:</strong></p>
                    <ul>
                        <li>Personal details (name, IC number, date of birth, gender)</li>
                        <li>Contact information</li>
                        <li>Disability type and support needs</li>
                        <li>Medical information relevant to rehabilitation</li>
                        <li>Guardian/parent information</li>
                        <li>Educational background</li>
                    </ul>

                    <p><strong>3.2 Staff Information:</strong></p>
                    <ul>
                        <li>Employee details (name, IC number, email, phone)</li>
                        <li>Professional qualifications</li>
                        <li>Centre assignment and role</li>
                    </ul>

                    <p><strong>3.3 Volunteer Information:</strong></p>
                    <ul>
                        <li>Personal details and contact information</li>
                        <li>Skills and availability</li>
                        <li>Background check results (where applicable)</li>
                    </ul>

                    <p><strong>3.4 Usage Data:</strong></p>
                    <ul>
                        <li>Activity enrollment and participation records</li>
                        <li>Attendance tracking</li>
                        <li>Communication logs</li>
                        <li>System access logs</li>
                    </ul>

                    <h5>4. How We Use Your Information</h5>
                    <p>We use collected information for:</p>
                    <ul>
                        <li>Providing rehabilitation services and support</li>
                        <li>Managing activities, schedules, and enrollments</li>
                        <li>Communicating with parents/guardians about trainee progress</li>
                        <li>Generating reports and documentation</li>
                        <li>Improving our services and programs</li>
                        <li>Ensuring safety and security of all participants</li>
                        <li>Complying with legal and regulatory requirements</li>
                    </ul>

                    <h5>5. Data Sharing and Disclosure</h5>
                    <p><strong>5.1 Internal Sharing:</strong> Information is shared only with authorized IIUM PD-CARE staff on a need-to-know basis.</p>
                    <p><strong>5.2 External Sharing:</strong> We may share information with:</p>
                    <ul>
                        <li>IIUM administration (for institutional reporting)</li>
                        <li>Government agencies (when legally required)</li>
                        <li>Healthcare providers (with consent, for trainee welfare)</li>
                        <li>Parents/guardians (regarding their child)</li>
                    </ul>
                    <p><strong>5.3 No Commercial Use:</strong> We never sell or rent personal information to third parties.</p>

                    <h5>6. Data Security</h5>
                    <p>We implement security measures including:</p>
                    <ul>
                        <li>Encrypted data storage</li>
                        <li>Secure user authentication</li>
                        <li>Role-based access control</li>
                        <li>Regular security audits</li>
                        <li>Staff training on data protection</li>
                    </ul>

                    <h5>7. Data Retention</h5>
                    <p>We retain personal data for:</p>
                    <ul>
                        <li>Active trainees: Duration of enrollment plus 2 years</li>
                        <li>Graduated/withdrawn trainees: 5 years for record purposes</li>
                        <li>Staff: Duration of employment plus 3 years</li>
                        <li>Volunteers: Duration of service plus 2 years</li>
                    </ul>

                    <h5>8. Your Rights</h5>
                    <p>Under PDPA 2010, you have the right to:</p>
                    <ul>
                        <li>Access your personal data</li>
                        <li>Request correction of inaccurate data</li>
                        <li>Withdraw consent (where applicable)</li>
                        <li>Request data deletion (subject to legal requirements)</li>
                        <li>Lodge a complaint with authorities</li>
                    </ul>

                    <h5>9. Children's Privacy</h5>
                    <p>We collect information about children (trainees) with parental/guardian consent. Parents/guardians have the right to review, modify, or request deletion of their child's information.</p>

                    <h5>10. Cookies and Tracking</h5>
                    <p>CREAMS uses essential cookies for system functionality and user authentication. We do not use tracking cookies for marketing purposes.</p>

                    <h5>11. Third-Party Services</h5>
                    <p>We may use third-party services (e.g., cloud storage, email services) that comply with data protection standards.</p>

                    <h5>12. Data Breach Notification</h5>
                    <p>In the event of a data breach, we will notify affected individuals and relevant authorities within 72 hours as required by law.</p>

                    <h5>13. Updates to Privacy Policy</h5>
                    <p>We may update this policy periodically. Significant changes will be communicated via email or system notification.</p>

                    <h5>14. Contact Us</h5>
                    <p>For privacy concerns or data access requests, contact:</p>
                    <p>
                        <strong>IIUM PD-CARE Data Protection Officer</strong><br>
                        Email: info@creams.org<br>
                        Phone: (+60) 3642 1633 5<br>
                        Address: International Islamic University Malaysia, Gombak, Selangor
                    </p>

                    <div class="alert alert-success mt-4">
                        <i class="fas fa-check-circle"></i> <strong>Your Privacy Matters:</strong> We are committed to protecting your personal information and handling it with the highest level of care and respect.
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
