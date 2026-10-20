{{-- resources/views/partials/legal-modals.blade.php
     Opened by any link with data-open="termsModal" or data-open="privacyModal".
     Sections are numbered automatically (CSS counter), so you can add, remove or
     reorder <div class="lg-sec"> blocks freely. Styles live in css/landing.css (".lg-*"). --}}

{{-- ============ TERMS & CONDITIONS ============ --}}
<div class="lg-overlay" id="termsModal" role="dialog" aria-modal="true" aria-labelledby="termsTitle" hidden>
    <div class="lg-modal">
        <div class="lg-head">
            <div>
                <h2 id="termsTitle">Terms &amp; Conditions</h2>
                <p>Pistavora Amusement Park &middot; Last updated October 2026</p>
            </div>
            <button type="button" class="lg-x" data-close aria-label="Close">&times;</button>
        </div>

        <div class="lg-body" tabindex="0">
            <p class="lg-intro">
                Please read these Terms &amp; Conditions before creating an account, booking or visiting Pistavora.
                By using this website or our venue, you agree to be bound by them.
            </p>

            <div class="lg-sections">

                <div class="lg-sec">
                    <h3>Acceptance of Terms</h3>
                    <p>
                        By accessing this website, creating an account or booking a visit with Pistavora Amusement Park
                        (&ldquo;Pistavora&rdquo;, &ldquo;we&rdquo;, &ldquo;us&rdquo;), you agree to these Terms and to our
                        <a href="#" data-open="privacyModal">Privacy Notice</a>. If you do not agree, please do not use the
                        website or make a booking.
                    </p>
                </div>

                <div class="lg-sec">
                    <h3>Your Account</h3>
                    <ul>
                        <li>Provide accurate and complete information when you register.</li>
                        <li>Keep your username and password confidential. You are responsible for all activity under your account.</li>
                        <li>Tell us right away if you suspect unauthorized use of your account.</li>
                        <li>We may suspend or close accounts that contain false information or are used improperly.</li>
                    </ul>
                </div>

                <div class="lg-sec">
                    <h3>Bookings and Confirmation</h3>
                    <ul>
                        <li>A booking is a request until our team reviews and confirms it.</li>
                        <li>All bookings depend on slot availability and venue capacity.</li>
                        <li>Walk-ins are welcome, depending on capacity.</li>
                        <li>Birthdays, school trips and group events are arranged in advance with our reservations team.</li>
                    </ul>
                </div>

                <div class="lg-sec">
                    <h3>Rates and Payments</h3>
                    <ul>
                        <li>Rates apply to the zone or pass you choose and may change during promos and seasonal events.</li>
                        <li>We accept cash, credit and debit cards, GCash, Maya, Klook vouchers and StarDeals vouchers.</li>
                        <li>Online payments are made by QR code. Upload your proof of payment and wait for approval before your voucher is issued.</li>
                        <li>Show your voucher code to the cashier on arrival.</li>
                        <li>Partner vouchers (Klook, StarDeals) are redeemed at the gate and are also subject to the partner&rsquo;s own terms.</li>
                    </ul>
                </div>

                <div class="lg-sec">
                    <h3>Safety and Health</h3>
                    <ul>
                        <li>Follow all posted ride rules, height and age limits, and staff instructions.</li>
                        <li>Guests with heart conditions, severe asthma, epilepsy, recent injuries or similar concerns should avoid extreme rides.</li>
                        <li>Pregnant guests are not allowed on extreme or high-impact rides.</li>
                        <li>Staff may refuse or stop any ride or activity for safety reasons.</li>
                    </ul>
                </div>

                <div class="lg-sec">
                    <h3>Minors and Guardians</h3>
                    <ul>
                        <li>Guests under 18 need parent or guardian consent. Consent can be given in person, by call, text or online chat.</li>
                        <li>DinoLakbay: only children 12 and under may use the play areas, and children aged 1 to 5 must always have a guardian with them.</li>
                        <li>Skataya: children aged 4 to 6 must have a guardian inside the rink, and the guardian must also wear roller skates.</li>
                        <li>Guardians must supervise young children at all times.</li>
                    </ul>
                </div>

                <div class="lg-sec">
                    <h3>Guest Conduct</h3>
                    <p>
                        Please treat staff, other guests and property with respect. Harassment, damaging property, entering
                        restricted areas and unsafe behavior are not allowed, and guests may be asked to leave.
                    </p>
                </div>

                <div class="lg-sec">
                    <h3>Changes and Cancellations</h3>
                    <p>
                        Requests to change or cancel a booking are subject to approval and to the terms that applied when you
                        booked. Please contact our reservations team as early as possible.
                    </p>
                </div>

                <div class="lg-sec">
                    <h3>Limitation of Liability</h3>
                    <p>
                        To the extent permitted by law, Pistavora is not liable for the loss, theft or damage of personal
                        belongings, or for injuries that result from not following ride rules, posted instructions or staff
                        directions. Nothing in these Terms limits any liability that cannot be limited by law.
                    </p>
                </div>

                <div class="lg-sec">
                    <h3>Changes to These Terms</h3>
                    <p>
                        We may update these Terms from time to time. The date at the top shows when they were last updated.
                        Continuing to use the website or visit the park after an update means you accept the new Terms.
                    </p>
                </div>

                <div class="lg-sec">
                    <h3>Contact Us</h3>
                   <p>
                        Questions about these Terms? Talk to our team at Lima Technology Center, 
                        Lipa City / Malvar, Batangas, reach us through this website, or email us at 
                        <a href="mailto:pistavoraamusement@gmail.com">pistavoraamusement@gmail.com</a>.
                    </p>
                </div>

            </div>
        </div>

        <div class="lg-foot">
            <button type="button" class="btn btn-primary" data-close>I Understand</button>
        </div>
    </div>
</div>

{{-- ============ PRIVACY NOTICE ============ --}}
<div class="lg-overlay" id="privacyModal" role="dialog" aria-modal="true" aria-labelledby="privacyTitle" hidden>
    <div class="lg-modal">
        <div class="lg-head">
            <div>
                <h2 id="privacyTitle">Privacy Notice</h2>
                <p>Pistavora Amusement Park &middot; Last updated October 2026</p>
            </div>
            <button type="button" class="lg-x" data-close aria-label="Close">&times;</button>
        </div>

        <div class="lg-body" tabindex="0">
            <p class="lg-intro">
                Pistavora respects your privacy. This notice explains what personal information we collect, why we collect it,
                and your rights under the Data Privacy Act of 2012 (Republic Act No. 10173).
            </p>

            <div class="lg-sections">

                <div class="lg-sec">
                    <h3>Information We Collect</h3>
                    <ul>
                        <li><strong>Account details:</strong> full name, username, email, age and password.</li>
                        <li><strong>Booking details:</strong> chosen zones or rides, date, time and number of guests.</li>
                        <li><strong>Payment records:</strong> proof-of-payment screenshots, payment references and voucher codes.</li>
                        <li><strong>Sign-in data:</strong> if you sign in with Google or Facebook, the name and email they share with us.</li>
                        <li><strong>Guardian consent:</strong> records of parent or guardian consent for guests under 18.</li>
                        <li><strong>Technical data:</strong> IP address, browser type and device information when you use the website.</li>
                    </ul>
                </div>

                <div class="lg-sec">
                    <h3>How We Use Your Information</h3>
                    <ul>
                        <li>Create and manage your account.</li>
                        <li>Review, confirm and verify bookings and payments, and issue vouchers.</li>
                        <li>Contact you about your booking, schedule changes or safety matters.</li>
                        <li>Check age and guardian consent requirements for rides and play areas.</li>
                        <li>Keep the website secure and prevent fraud or misuse.</li>
                        <li>Meet our legal and accounting obligations.</li>
                    </ul>
                </div>

                <div class="lg-sec">
                    <h3>Consent and Legal Basis</h3>
                    <p>
                        We process your information with your consent, to carry out your booking, and to comply with the law.
                        You may withdraw your consent at any time, subject to existing bookings and legal requirements.
                    </p>
                </div>

                <div class="lg-sec">
                    <h3>Sharing of Information</h3>
                    <p>We do not sell your personal information. We share it only with:</p>
                    <ul>
                        <li>Payment and e-wallet providers that process your payment.</li>
                        <li>Google or Facebook, when you choose to sign in with them.</li>
                        <li>Klook and StarDeals, for bookings made through those partners.</li>
                        <li>Hosting and IT service providers that help us run the website.</li>
                        <li>Government agencies or law enforcement when the law requires it.</li>
                    </ul>
                </div>

                <div class="lg-sec">
                    <h3>Storage and Retention</h3>
                    <p>
                        We keep your information only as long as needed for the purposes above and for legal or accounting
                        requirements. After that, we securely delete or anonymize it.
                    </p>
                </div>

                <div class="lg-sec">
                    <h3>Security</h3>
                    <p>
                        We use organizational, physical and technical measures to protect your information from loss, misuse and
                        unauthorized access. No system is completely secure, so please keep your password confidential.
                    </p>
                </div>

                <div class="lg-sec">
                    <h3>Your Rights</h3>
                    <p>Under the Data Privacy Act, you have the right to:</p>
                    <ul>
                        <li>Be informed about how your data is processed.</li>
                        <li>Access the personal information we hold about you.</li>
                        <li>Object to processing and withdraw your consent.</li>
                        <li>Correct inaccurate or outdated information.</li>
                        <li>Request erasure or blocking of your data.</li>
                        <li>Receive a copy of your data in a usable format.</li>
                        <li>Claim damages where appropriate, and file a complaint with the National Privacy Commission (privacy.gov.ph).</li>
                    </ul>
                </div>

                <div class="lg-sec">
                    <h3>Children&rsquo;s Privacy</h3>
                    <p>
                        For guests under 18, we collect and use information only with the consent of a parent or guardian.
                        Parents and guardians may ask us to review or delete their child&rsquo;s information.
                    </p>
                </div>

                <div class="lg-sec">
                    <h3>Changes to This Notice</h3>
                    <p>
                        We may update this notice from time to time. The date at the top shows when it was last updated.
                    </p>
                </div>

                <div class="lg-sec">
                    <h3>Contact Us</h3>
                     <p>
                        To use your rights or ask a question about your data, talk to our team at 
                        Lima Technology Center, Lipa City / Malvar, Batangas, reach us through this 
                        website, or email us at 
                        <a href="mailto:pistavoraamusement@gmail.com">pistavoraamusement@gmail.com</a>. 
                        You may also ask for our Data Protection Officer. You can read our 
                        <a href="#" data-open="termsModal">Terms &amp; Conditions</a>.
                    </p>
                </div>

            </div>
        </div>

        <div class="lg-foot">
            <button type="button" class="btn btn-primary" data-close>I Understand</button>
        </div>
    </div>
</div>

<script>
    (function () {
        var lastFocus = null;

        function openModal(id) {
            var modal = document.getElementById(id);
            if (!modal) return;
            document.querySelectorAll('.lg-overlay:not([hidden])').forEach(function (m) { m.hidden = true; });
            if (!lastFocus) lastFocus = document.activeElement;
            modal.hidden = false;
            document.body.classList.add('lg-lock');
            modal.querySelector('.lg-body').scrollTop = 0;
            modal.querySelector('.lg-x').focus();
        }

        function closeModal(modal) {
            modal.hidden = true;
            document.body.classList.remove('lg-lock');
            if (lastFocus && lastFocus.focus) lastFocus.focus();
            lastFocus = null;
        }

        document.addEventListener('click', function (e) {
            var opener = e.target.closest('[data-open]');
            if (opener && document.getElementById(opener.getAttribute('data-open'))) {
                e.preventDefault();
                openModal(opener.getAttribute('data-open'));
                return;
            }
            var closer = e.target.closest('[data-close]');
            if (closer) { closeModal(closer.closest('.lg-overlay')); return; }
            if (e.target.classList && e.target.classList.contains('lg-overlay')) closeModal(e.target);
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                document.querySelectorAll('.lg-overlay:not([hidden])').forEach(closeModal);
            }
        });
    })();
</script>