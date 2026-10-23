{{-- resources/views/landing.blade.php --}}
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <title>Pistavora | Family Amusement Center</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link
        href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@500;600;700;800&family=Inter:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/landing.css') }}">
</head>

<body>

    <header>
        <nav>
            <div class="logo">
                <img src="{{ asset('images/pistavora-logo-horizontal.svg') }}" alt="Pistavora"
                    style="height:50px;vertical-align:middle; margin:0; mix-blend-mode: multiply;">
            </div>
            <div class="nav-links">
                <a href="#passes">Day Passes</a>
                <a href="#attractions">Attractions</a>
                <a href="#services">Services</a>
                <a href="#contact">Visit Us</a>
                <a href="#about" onclick="event.preventDefault(); openAbout();">About Us</a>
            </div>
            <div style="display:flex;gap:10px;">
                <a href="{{ route('login') }}" class="btn btn-outline btn-sm">Sign In</a>
                <a href="{{ route('login') }}" class="btn btn-primary btn-sm">Book Now</a>
            </div>
        </nav>
    </header>

    {{-- ============ HERO ============ --}}
    <section class="hero">
        <video id="wpTeaserVideo" class="hero-bg-video" autoplay muted loop playsinline
            poster="{{ asset('images/wonderpark-teaser-poster.jpg') }}">
            <source src="{{ asset('videos/landing.mp4') }}" type="video/mp4">
        </video>
        <div class="hero-video-overlay"></div>

        <div class="wrap">
            <div class="hero-grid">
                <div class="hero-copy">
                    <div class="wp-open"><b><i></i>Open Daily</b><span>Holidays too</span></div>
                    <h1>Rides, Roller Skating<br>and a DinoLakbay.</h1>
                    <p>
                        Pistavora has three zones for the whole family. Reserve your slot and pay online, or walk in.
                        Passes start at
                        <a href="#passes"
                            style="font-weight:700;color:var(--amber);text-decoration:underline;text-underline-offset:3px;">₱149</a>.
                    </p>
                    <div class="hero-cta">
                        <a href="#passes" class="btn btn-primary">Reserve a Slot</a>
                        <a href="#attractions" class="btn btn-outline-light">See Attractions</a>
                    </div>
                    <p class="wp-zones-line" aria-hidden="true">
                        <span><i style="background:var(--wp-coral)"></i>PiyestaRides</span>
                        <span><i style="background:var(--wp-teal)"></i>Skataya</span>
                        <span><i style="background:var(--wp-amber)"></i>DinoLakbay</span>
                    </p>
                </div>
            </div>
        </div>

        @php
            $tick = ['Open Daily', 'Including holidays', 'Weekends 10:00 AM to 9:00 PM', 'Weekdays 11:00 AM to 9:00 PM', 'PiyestaRides', 'Skataya', 'DinoLakbay', 'Walk-ins welcome'];
        @endphp
        <div class="wp-ticker" aria-label="Open daily, including holidays. Weekends 10 AM to 9 PM. Weekdays 11 AM to 9 PM.">
            <div class="wp-tick-track">
                @for ($c = 0; $c < 4; $c++)
                    <div class="wp-tick-group" @if ($c) aria-hidden="true" @endif>
                        @foreach ($tick as $k => $label)
                            <span class="{{ $k === 0 ? 'open' : '' }}">{{ $label }}</span><i></i>
                        @endforeach
                    </div>
                @endfor
            </div>
        </div>

        <div class="hero-video-meta">
            <span class="hero-video-badge"><span class="dot"></span>At Pistavora</span>
            <button type="button" class="hero-video-sound" id="wpTeaserSound" aria-label="Toggle video sound"
                onclick="toggleWpSound()">
                <svg id="wpSoundIcon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round"></svg>
            </button>
        </div>
    </section>

    {{-- ============ DAY PASSES ============ --}}
    <section id="passes">
        <div class="wrap">
            <div class="section-head reveal">
                <span class="ticket-label coral">Admission Rates</span>
                <h2>Pick the pass that fits your day</h2>
                <p>All-day passes include unlimited play in your zone. Rates can change during promos and seasonal events.</p>
            </div>

            <div class="wp-passes">

                <div class="wp-pass" style="--c:#8a6a2f;--b:#5b4526" onclick="openWpModal('pass-dino')">
                    <span class="zone">DinoLakbay</span>
                    <h3>Dino Day Pass</h3>
                    <div class="price">₱599<small>per pass</small></div>
                    <ul>
                        <li>Unlimited all-day access</li>
                        <li>Includes 1 child and 1 guardian</li>
                        <li>All DinoLakbay play areas</li>
                        <li>Best for ages 4 to 12</li>
                    </ul>
                    <div class="links">
                        <a href="{{ route('login') }}" onclick="event.stopPropagation();">Book now</a>
                        <button type="button" onclick="event.stopPropagation(); openWpModal('pass-dino');">See services</button>
                    </div>
                </div>

                <div class="wp-pass top" style="--c:#2dd4bf" onclick="openWpModal('pass-roller')">
                    <span class="flag">Most popular</span>
                    <span class="zone">Skataya</span>
                    <h3>Skataya Pass</h3>
                    <div class="price">₱599<small>per pass</small></div>
                    <ul>
                        <li>Unlimited all-day skating</li>
                        <li>Open to kids, teens and adults</li>
                        <li>Great for families and groups</li>
                        <li>Skate rental included</li>
                    </ul>
                    <div class="links">
                        <a href="{{ route('login') }}" onclick="event.stopPropagation();">Book now</a>
                        <button type="button" onclick="event.stopPropagation(); openWpModal('pass-roller');">See services</button>
                    </div>
                </div>

                <div class="wp-pass" style="--c:#9a4a52;--b:#6b3a40" onclick="openWpModal('pass-fields')">
                    <span class="zone">PiyestaRides</span>
                    <h3>Ride-All-You-Can</h3>
                    <div class="price">
                        <s class="was">₱1,590</s>
                        <span class="now">₱999</span>
                        <small>per pass</small>
                    </div>
                    <ul>
                        <li>Ride-all-you-can packages</li>
                        <li>Discounts on Klook and StarDeals</li>
                        <li>Seasonal and event promos</li>
                        <li>Ask staff for current rates</li>
                    </ul>
                    <div class="links">
                        <a href="{{ route('login') }}" onclick="event.stopPropagation();">Book now</a>
                        <button type="button" onclick="event.stopPropagation(); openWpModal('pass-fields');">See rides</button>
                    </div>
                </div>

            </div>
        </div>
    </section>

    {{-- ============ ATTRACTIONS ============ --}}
    <section id="attractions">
        <div class="wrap">
            <div class="section-head reveal">
                <span class="ticket-label coral">Attractions</span>
                <h2>Pick your adventure</h2>
                <p>Each zone has its own rides, staff and queue. Swipe to look around.</p>
            </div>

            <div class="wp-attr-wrap">
            <div class="wp-attr" id="ridesCarousel">

                <article class="wp-card" style="--tint:#3a1519;--accent:#e11d48">
                    <div class="wp-photo" data-bg="{{ asset('images/vikings.jpg') }}"></div>
                    <div class="wp-body">
                        <span class="tag">PiyestaRides</span>
                        <h3>PiyestaRides</h3>
                        <p>Amusement rides for every age, from gentle family rides to high-thrill ones.</p>
                        <div class="wp-facts"><span>Many attractions</span><span>Ride-specific rules</span></div>
                        <details>
                            <summary>Good to know</summary>
                            <ul>
                                <li>Eligibility depends on each ride's height and safety rules.</li>
                                <li>Guests with heart conditions, severe asthma, epilepsy, recent injuries or similar concerns should avoid extreme rides.</li>
                                <li>Pregnant guests are not allowed on extreme or high-impact rides.</li>
                                <li>Guests under 18 need parent or guardian consent.</li>
                            </ul>
                        </details>
                    </div>
                </article>

                <article class="wp-card" style="--tint:#0d2f2c;--accent:#0f766e">
                    <div class="wp-photo" data-bg="{{ asset('images/roller-fever.jpg') }}"></div>
                    <div class="wp-body">
                        <span class="tag">Skataya</span>
                        <h3>Skataya Skating Rink</h3>
                        <p>All-day roller skating in a safe, family-friendly rink. Good for first-timers and regular skaters.</p>
                        <div class="wp-facts"><span>All-day access</span><span>Family friendly</span></div>
                        <details>
                            <summary>Good to know</summary>
                            <ul>
                                <li>Children aged 4 to 6 must have a guardian with them inside the rink.</li>
                                <li>Guardians on the rink must also wear roller skates.</li>
                                <li>Guests under 18 need parent or guardian consent.</li>
                                <li>Consent can be given in person, by call, text or online chat.</li>
                            </ul>
                        </details>
                    </div>
                </article>

                <article class="wp-card" style="--tint:#3a2a08;--accent:#b45309">
                    <div class="wp-photo" data-bg="{{ asset('images/dino_1.jpg') }}"></div>
                    <div class="wp-body">
                        <span class="tag">DinoLakbay</span>
                        <h3>DinoLakbay Playground</h3>
                        <p>An indoor playground with a dinosaur theme, with slides, climbing areas, obstacle courses and play zones.</p>
                        <div class="wp-facts"><span>Up to age 12</span><span>Indoor playground</span></div>
                        <details>
                            <summary>Good to know</summary>
                            <ul>
                                <li>Children aged 1 to 5 must always have a guardian with them.</li>
                                <li>Only children 12 and under can use the play areas.</li>
                                <li>Guardians supervise young children at all times.</li>
                                <li>Guests under 18 need parent or guardian consent.</li>
                            </ul>
                        </details>
                    </div>
                </article>

                <article class="wp-card" style="--tint:#2a1c46;--accent:#7c3aed">
                    <div class="wp-photo" data-bg="{{ asset('images/roller-skates.jpg') }}"></div>
                    <div class="wp-body">
                        <span class="tag">Party add-on</span>
                        <h3>Celebrate at Pistavora</h3>
                        <p>Birthdays and group parties fit both Skataya and DinoLakbay. Skate by the neon photo corner or gather the kids around the giant ball pit.</p>
                        <div class="wp-facts"><span>All ages</span><span>Book ahead</span></div>
                        <button type="button" class="more" onclick="openWpModal('celebrate')">Party details</button>
                    </div>
                </article>

            </div>
            <div class="wp-dots" id="wpDots" role="tablist" aria-label="Attractions"></div>
            </div>
        </div>
    </section>

    {{-- ============ SERVICES ============ --}}
    <section id="services">
        <div class="wrap">
            <div class="section-head reveal">
                <span class="ticket-label amber">Guest Services</span>
                <h2>Everything for your visit</h2>
                <p>Parties, easy booking, many ways to pay and food on site.</p>
            </div>

            <div class="modules stagger">

                <div class="module-card" onclick="openWpModal('party')">
                    <div class="module-icon">🎉</div>
                    <h3>Birthday & Party Packages</h3>
                    <p>Hold birthdays, school outings, team events and family gatherings at PiyestaRides, DinoLakbay or Skataya.</p>
                </div>

                <div class="module-card" onclick="openWpModal('reservation')">
                    <div class="module-icon">📝</div>
                    <h3>Reservation & Confirmation</h3>
                    <p>Send your booking request and event details. Our team reviews it and confirms based on availability.</p>
                </div>

                <div class="module-card" onclick="openWpModal('channels')">
                    <div class="module-icon">🌐</div>
                    <h3>Multiple Booking Channels</h3>
                    <p>Book with Pistavora directly, or through Klook and StarDeals for selected attractions and promos.</p>
                </div>

                <div class="module-card" onclick="openWpModal('payment')">
                    <div class="module-icon">💳</div>
                    <h3>Flexible Payment Options</h3>
                    <p>We take cash, credit and debit cards, GCash, Maya, Klook vouchers and StarDeals vouchers.</p>
                </div>

                <div class="module-card" onclick="openWpModal('snacks')">
                    <div class="module-icon">🍿</div>
                    <h3>Snack Bar & Refreshments</h3>
                    <p>Snacks and drinks are sold inside the venue, so nobody has to leave to refuel.</p>
                </div>

            </div>
        </div>
    </section>

    {{-- ============ HOW IT WORKS ============ --}}
    <section id="how">
        <div class="wrap">
            <div class="section-head reveal">
                <span class="ticket-label violet">How it works</span>
                <h2>From account to cashier in 6 steps</h2>
                <p>Tap through each step and try it. Nothing here is saved.</p>
            </div>

            <div class="wp-how">
                <div class="wp-rail" role="tablist" aria-label="Steps">
                    <button type="button" class="wp-step on" role="tab" style="--c:#e11d48"><div class="n"><span>1</span></div><div><h4>Create an account</h4><p>Sign up on this website.</p></div></button>
                    <button type="button" class="wp-step" role="tab" style="--c:#d97706"><div class="n"><span>2</span></div><div><h4>Log in</h4><p>Open the Booking page.</p></div></button>
                    <button type="button" class="wp-step" role="tab" style="--c:#0f766e"><div class="n"><span>3</span></div><div><h4>Choose your rides</h4><p>Solo, couple or group.</p></div></button>
                    <button type="button" class="wp-step" role="tab" style="--c:#7c3aed"><div class="n"><span>4</span></div><div><h4>Pick date and pay</h4><p>Pay by QR online.</p></div></button>
                    <button type="button" class="wp-step" role="tab" style="--c:#e11d48"><div class="n"><span>5</span></div><div><h4>Upload proof</h4><p>Wait for approval.</p></div></button>
                    <button type="button" class="wp-step" role="tab" style="--c:#d97706"><div class="n"><span>6</span></div><div><h4>Get your voucher</h4><p>Show it to the cashier.</p></div></button>
                </div>

                <div class="wp-stage">

                    <div class="wp-pane on">
                        <h3>Create an account</h3>
                        <p class="lead">You need an account on this website before you can book.</p>
                        <div class="wp-field">
                            <div><label for="wpN">Name</label><input id="wpN" type="text" placeholder="Juan Dela Cruz" autocomplete="off"></div>
                            <div><label for="wpE">Email</label><input id="wpE" type="email" placeholder="you@email.com" autocomplete="off"></div>
                        </div>
                        <button type="button" class="wp-act" id="wpCreate">Create account</button>
                        <div class="wp-note" id="wpCreateNote">This is a practice form. To make your real account, use Sign In at the top of the page.</div>
                        <div class="foot"><span></span><button type="button" onclick="wpGoStep(1)">Next</button></div>
                    </div>

                    <div class="wp-pane">
                        <h3>Log in and open Booking</h3>
                        <p class="lead">Log in to your new account, then go to the Booking page.</p>
                        <div class="wp-appbar" id="wpBar">
                            <button type="button" class="on" data-t="home">Home</button>
                            <button type="button" data-t="login">Log in</button>
                            <button type="button" data-t="booking" disabled>Booking</button>
                        </div>
                        <div class="wp-note" id="wpBarNote">Tap Log in first.</div>
                        <div class="foot"><button type="button" onclick="wpGoStep(0)">Back</button><button type="button" onclick="wpGoStep(2)">Next</button></div>
                    </div>

                    <div class="wp-pane">
                        <h3>Choose your rides</h3>
                        <p class="lead">Pick what you want to enjoy. Come solo, as a couple or with a group of friends.</p>
                        <p class="wp-label">Who is coming?</p>
                        <div class="wp-chipset" id="wpWho">
                            <button type="button" class="on">Solo</button>
                            <button type="button">Couple</button>
                            <button type="button">Group of friends</button>
                        </div>
                        <p class="wp-label">Pick any rides you like</p>
                        <div class="wp-ridepick" id="wpPick">
                            <button type="button" data-v="Field of Rides">🎡<small>PiyestaRides</small></button>
                            <button type="button" data-v="Roller Fever">🛼<small>Skataya</small></button>
                            <button type="button" data-v="Dino Adventure">🦕<small>DinoLakbay</small></button>
                        </div>
                        <div class="wp-note" id="wpPickNote">Solo, no rides picked yet.</div>
                        <div class="foot"><button type="button" onclick="wpGoStep(1)">Back</button><button type="button" onclick="wpGoStep(3)">Next</button></div>
                    </div>

                    <div class="wp-pane">
                        <h3>Pick a date and time, then pay</h3>
                        <p class="lead">Payment is by QR code, online.</p>
                        <p class="wp-label">Date</p>
                        <div class="wp-chipset" id="wpDates"></div>
                        <p class="wp-label">Time</p>
                        <div class="wp-chipset" id="wpTimes">
                            <button type="button" class="on">11:00 AM</button>
                            <button type="button">1:00 PM</button>
                            <button type="button">3:00 PM</button>
                            <button type="button">5:00 PM</button>
                        </div>
                        <div class="wp-payrow" id="wpPayRow" hidden>
                            <div class="wp-phone"><div class="wp-qr" id="wpQr"></div></div>
                            <p>Scan this with your banking or e-wallet app and pay. Keep a screenshot of the receipt for the next step.</p>
                        </div>
                        <button type="button" class="wp-act" id="wpPay">Show payment QR</button>
                        <div class="foot"><button type="button" onclick="wpGoStep(2)">Back</button><button type="button" onclick="wpGoStep(4)">Next</button></div>
                    </div>

                    <div class="wp-pane">
                        <h3>Upload your proof of payment</h3>
                        <p class="lead">Send a screenshot of your receipt, then wait for approval.</p>
                        <div class="wp-drop" id="wpDrop" tabindex="0" role="button">📎 Tap to attach your receipt screenshot</div>
                        <div class="wp-bar" id="wpBarWrap" hidden><i id="wpBarFill"></i></div>
                        <div class="wp-note" id="wpProofNote">Our team checks every payment before approving it.</div>
                        <div class="foot"><button type="button" onclick="wpGoStep(3)">Back</button><button type="button" onclick="wpGoStep(5)">Next</button></div>
                    </div>

                    <div class="wp-pane">
                        <h3>Get your voucher</h3>
                        <p class="lead">Once your payment is approved, you receive a voucher code. Show it to the cashier on site.</p>
                        <div class="wp-voucher locked" id="wpVoucher">
                            <small>Voucher code</small>
                            <div class="code" id="wpCode">WP-0000-00</div>
                            <small>Show this to the cashier</small>
                        </div>
                        <button type="button" class="wp-act" id="wpReveal" style="margin-top:16px">Reveal my voucher</button>
                        <div class="wp-note" id="wpVNote">Your voucher shows up after your payment is approved.</div>
                        <div class="foot"><button type="button" onclick="wpGoStep(4)">Back</button><a href="{{ route('login') }}" style="font-weight:700;">Start your booking</a></div>
                    </div>

                </div>
            </div>
        </div>
    </section>

    {{-- ============ VISIT US ============ --}}
    <section id="contact">
        <div class="wrap">
            <div class="section-head reveal">
                <h2>Plan your visit</h2>
                <p>Group bookings and school trips are set up in advance with our reservations team.</p>
            </div>

            <div class="wp-visit-grid">

                <div class="wp-tile wp-status" id="wpStatus">
                    <div>
                        <h3>Right now</h3>
                        <div class="big"><i></i><span id="wpStatusMain">Checking</span></div>
                        <p class="sub" id="wpStatusSub"></p>
                    </div>
                    <a href="{{ route('login') }}">Book a slot</a>
                </div>

                <div class="wp-tile wp-hours">
                    <h3>Hours</h3>
                    <dl>
                        <div class="row" data-days="1,2,3,4,5"><dt>Weekdays</dt><dd style="margin:0">11:00 AM to 9:00 PM</dd></div>
                        <div class="row" data-days="0,6"><dt>Weekends</dt><dd style="margin:0">10:00 AM to 9:00 PM</dd></div>
                    </dl>
                    <p class="note">Open every day, holidays included.</p>
                </div>

                <div class="wp-tile wp-map">
                    <iframe title="Map to Pistavora" loading="lazy"
                        src="https://maps.google.com/maps?q=Lima+Technology+Center+Malvar+Batangas&output=embed"></iframe>
                    <div class="addr">
                        <span>Lima Technology Center, Lipa City / Malvar, Batangas</span>
                        <a href="https://www.google.com/maps/search/?api=1&query=Lima+Technology+Center+Malvar+Batangas"
                            target="_blank" rel="noopener">Get directions</a>
                    </div>
                </div>

                <div class="wp-tile wp-good">
                    <dl style="margin:0;display:grid;gap:18px;">
                        <div><dt>Zones</dt><dd><div class="wp-chips"><span>PiyestaRides</span><span>Skataya</span><span>DinoLakbay</span></div></dd></div>
                        <div><dt>Tickets</dt><dd>Reserve online or walk in, depending on capacity.</dd></div>
                        <div><dt>Pay with</dt><dd><div class="wp-chips"><span>Cash</span><span>Cards</span><span>GCash</span><span>Maya</span><span>Klook</span><span>StarDeals</span></div></dd></div>
                    </dl>
                </div>

            </div>
        </div>
    </section>

    {{-- ============ FOOTER ============ --}}
    <footer class="wp-foot">
        <div class="wp-foot-grid">
            <div class="wp-foot-brand">
                <p class="name">Pistavora</p>
                <p>Rides, roller skating and a dinosaur playground. A day out for the whole family.</p>
                <a href="{{ route('login') }}" class="btn btn-primary btn-sm">Book Now</a>
            </div>

            <div>
                <h4>Explore</h4>
                <ul>
                    <li><a href="#passes">Day Passes</a></li>
                    <li><a href="#attractions">Attractions</a></li>
                    <li><a href="#services">Services</a></li>
                    <li><a href="#how">How it works</a></li>
                    <li><a href="#contact">Visit Us</a></li>
                    <li><a href="#about" onclick="event.preventDefault(); openAbout();">About Us</a></li>
                </ul>
            </div>

            <div>
                <h4>Zones</h4>
                <ul>
                    <li><i style="background:var(--wp-coral)"></i>PiyestaRides</li>
                    <li><i style="background:var(--wp-teal)"></i>Skataya</li>
                    <li><i style="background:var(--wp-amber)"></i>DinoLakbay</li>
                    <li><a href="{{ route('login') }}">Sign In</a></li>
                </ul>
            </div>

            <div>
                <h4>Find us</h4>
                <ul>
                    <li>Lima Technology Center, Lipa City / Malvar, Batangas</li>
                    <li>Weekdays 11 AM to 9 PM<br>Weekends 10 AM to 9 PM</li>
                    <li><div class="chips"><span>Cash</span><span>Cards</span><span>GCash</span><span>Maya</span></div></li>
                </ul>
            </div>
        </div>

        <div class="wp-foot-bar">© {{ date('Y') }} Pistavora. All rights reserved. For educational purposes only.</div>
    </footer>

    {{-- ============ ABOUT US / DISCLAIMER MODAL ============ --}}
    <div class="ab-overlay" id="aboutOverlay" role="dialog" aria-modal="true" aria-labelledby="aboutTitle" hidden
        onclick="if(event.target===this) closeAbout()">
        <div class="ab-modal">
            <button type="button" class="ab-x" onclick="closeAbout()" aria-label="Close">&times;</button>

            <div class="ab-head">
                <span class="ab-tag">About Us</span>
                <h2 id="aboutTitle">About Us &amp; Disclaimer</h2>
                <p>Please read this before using the website.</p>
            </div>

            <div class="ab-body">
                <div class="ab-alert">
                    <strong>Educational purposes only.</strong>
                    This system is a school project and is not a real, operating business.
                </div>

                <h3>Disclaimer</h3>
                <ul>
                    <li>This website and system were made <strong>for educational purposes only</strong> as part of an academic requirement.</li>
                    <li>Pistavora is a <strong>fictional, inspired amusement center</strong>. It is inspired by real family amusement centers, but it is not the official website or system of any real company.</li>
                    <li>The brand names, zones, rides, rates, promos, hours, photos, videos, vouchers and payment steps shown here are samples for demonstration. They should not be treated as real offers.</li>
                    <li>Please do not enter real personal, payment or banking information. Any payment, QR code and voucher shown here is for demonstration only.</li>
                    <li>Any similarity to real businesses, brands, persons or events is unintentional or used only as inspiration for the project.</li>
                    <li>We do not claim ownership of third-party names, logos or media that may appear, and they belong to their respective owners.</li>
                </ul>

                <h3>About this project</h3>
                <p>
                    Pistavora is a sample family amusement center with three zones: PiyestaRides, Skataya and DinoLakbay.
                    The website shows how an amusement center could handle online booking, QR payment, proof of payment
                    approval and voucher claiming in one system.
                </p>

                <h3>Work with us</h3>
                <p>
                    We are also open to building a system for your business. If you are a client and want a booking,
                    ticketing, point of sale or business management system made for you, we are willing to accept
                    your project.
                </p>
                <p class="ab-mail">
                    Email us at
                    <a href="mailto:ancilmacalalad14@gmail.com">ancilmacalalad14@gmail.com</a>
                </p>
            </div>

            <div class="ab-foot">
                <button type="button" class="btn btn-primary btn-sm" onclick="closeAbout()">I understand</button>
            </div>
        </div>
    </div>

    {{-- ============ MODAL ============ --}}
    <div class="wp-modal-overlay" id="wpModalOverlay" onclick="if(event.target===this) closeWpModal()">
        <div class="wp-modal">
            <button class="wp-modal-close" onclick="closeWpModal()" aria-label="Close">&times;</button>
            <span class="wp-modal-icon" id="wpModalIcon"></span>
            <span class="wp-modal-tag" id="wpModalTag"></span>
            <h3 id="wpModalTitle"></h3>
            <p class="wp-modal-desc" id="wpModalDesc"></p>
            <ul id="wpModalList"></ul>
            <a href="{{ route('login') }}" class="btn btn-primary btn-sm"
                style="display:block;width:100%;text-align:center;margin-top:18px;box-sizing:border-box;">Book Now</a>
        </div>
    </div>

    <script>
        // Background images
        document.querySelectorAll('[data-bg]').forEach(el => {
            el.style.backgroundImage = `url('${el.dataset.bg}')`;
        });

        /* ---------- About Us / Disclaimer modal (opens on page load) ---------- */
        const aboutOverlay = document.getElementById('aboutOverlay');

        function openAbout() {
            aboutOverlay.hidden = false;
            document.body.style.overflow = 'hidden';
            aboutOverlay.querySelector('.ab-body').scrollTop = 0;
        }

        function closeAbout() {
            aboutOverlay.hidden = true;
            document.body.style.overflow = '';
        }

        // Show the disclaimer as soon as the page opens
        openAbout();

        /* ---------- Hero video + sound (tries to start with sound) ---------- */
        const wpVid = document.getElementById('wpTeaserVideo');
        const wpSoundBtn = document.getElementById('wpTeaserSound');
        const wpIcon = document.getElementById('wpSoundIcon');
        const ICON_OFF = '<polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon><line x1="23" y1="9" x2="17" y2="15"></line><line x1="17" y1="9" x2="23" y2="15"></line>';
        const ICON_ON = '<polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon><path d="M15.54 8.46a5 5 0 0 1 0 7.07"></path><path d="M19.07 4.93a10 10 0 0 1 0 14.14"></path>';

        function paintSound() {
            const on = !wpVid.muted;
            wpIcon.innerHTML = on ? ICON_ON : ICON_OFF;
            wpSoundBtn.classList.toggle('on', on);
        }

        function toggleWpSound() {
            wpVid.muted = !wpVid.muted;
            if (wpVid.paused) wpVid.play().catch(() => {});
            paintSound();
        }

        // Browsers block autoplay with sound. Try it, and if blocked, turn sound on at the first tap or key press.
        (function initSound() {
            wpVid.muted = false;
            const p = wpVid.play();
            const fallback = () => {
                wpVid.muted = true;
                wpVid.play().catch(() => {});
                paintSound();
                const unlock = () => {
                    wpVid.muted = false;
                    wpVid.play().catch(() => {});
                    paintSound();
                    ['pointerdown', 'keydown', 'touchend'].forEach(e => window.removeEventListener(e, unlock));
                };
                ['pointerdown', 'keydown', 'touchend'].forEach(e => window.addEventListener(e, unlock, { once: true }));
            };
            if (p && p.catch) p.catch(fallback);
            setTimeout(() => { if (wpVid.paused) fallback(); }, 400);
            paintSound();
        })();

        /* ---------- Attractions carousel: endless and auto-sliding ---------- */
        (function () {
            const track = document.getElementById('ridesCarousel');
            const dotsEl = document.getElementById('wpDots');
            const originals = [...track.querySelectorAll('.wp-card')];
            const n = originals.length;

            // clone the set before and after so the loop never ends
            const clone = c => {
                const k = c.cloneNode(true);
                k.setAttribute('aria-hidden', 'true');
                k.querySelectorAll('a,button,summary').forEach(x => x.tabIndex = -1);
                return k;
            };
            originals.forEach(c => track.insertBefore(clone(c), originals[0]));
            originals.forEach(c => track.appendChild(clone(c)));
            const cards = [...track.querySelectorAll('.wp-card')];

            const step = () => cards[1].offsetLeft - cards[0].offsetLeft;
            const setW = () => cards[n].offsetLeft - cards[0].offsetLeft;
            const leftFor = c => c.offsetLeft - (track.clientWidth - c.offsetWidth) / 2;
            // Own eased animation: native smooth scroll fights scroll-snap and looks jerky.
            let anim = 0, animating = false;
            const ease = t => t < .5 ? 4 * t * t * t : 1 - Math.pow(-2 * t + 2, 3) / 2;
            const stopAnim = () => {
                cancelAnimationFrame(anim);
                if (animating) { animating = false; track.style.scrollSnapType = ''; }
            };
            const slideTo = (target, dur) => {
                stopAnim();
                const from = track.scrollLeft, dist = target - from, t0 = performance.now();
                if (Math.abs(dist) < 1) return;
                animating = true;
                track.style.scrollSnapType = 'none';
                const tick = now => {
                    const p = Math.min((now - t0) / dur, 1);
                    track.scrollLeft = from + dist * ease(p);
                    if (p < 1) anim = requestAnimationFrame(tick);
                    else { animating = false; track.style.scrollSnapType = ''; paint(); recenter(); }
                };
                anim = requestAnimationFrame(tick);
            };
            const goTo = (i, smooth) => smooth
                ? slideTo(leftFor(cards[i]), 900)
                : track.scrollTo({ left: leftFor(cards[i]), behavior: 'auto' });
            const jump = d => {
                track.style.scrollSnapType = 'none';
                track.scrollLeft += d;
                requestAnimationFrame(() => { track.style.scrollSnapType = ''; });
            };

            let holdUntil = 0, hovering = false, down = false, visible = true, best = n, ticking = false, settle;
            const hold = () => { holdUntil = Date.now() + 7000; };

            originals.forEach((c, i) => {
                const b = document.createElement('button');
                b.type = 'button';
                b.setAttribute('aria-label', 'Go to card ' + (i + 1));
                b.onclick = () => { hold(); goTo(n + i, true); };
                dotsEl.appendChild(b);
            });

            function paint() {
                ticking = false;
                const box = track.getBoundingClientRect();
                const mid = box.left + box.width / 2;
                let bd = Infinity;
                cards.forEach((c, i) => {
                    const r = c.getBoundingClientRect();
                    const off = (r.left + r.width / 2 - mid) / r.width;
                    const d = Math.abs(off);
                    c.style.transform = `scale(${1 - Math.min(d, 1) * 0.07})`;
                    c.querySelector('.wp-photo').style.transform = `translateX(${off * -28}px) scale(${1.04 + (1 - Math.min(d, 1)) * 0.04})`;
                    if (d < bd) { bd = d; best = i; }
                });
                [...dotsEl.children].forEach((d, i) => d.classList.toggle('on', i === best % n));
            }

            function recenter() {
                if (down || animating) return;
                if (best < n) jump(setW());
                else if (best >= 2 * n) jump(-setW());
            }

            track.addEventListener('scroll', () => {
                if (!ticking) { ticking = true; requestAnimationFrame(paint); }
                clearTimeout(settle);
                settle = setTimeout(recenter, 150);
            }, { passive: true });

            ['pointerdown', 'touchstart'].forEach(e => track.addEventListener(e, () => { stopAnim(); down = true; hold(); }, { passive: true }));
            ['pointerup', 'pointercancel', 'touchend'].forEach(e => window.addEventListener(e, () => {
                if (down) { down = false; clearTimeout(settle); settle = setTimeout(recenter, 250); }
            }));
            ['wheel', 'keydown', 'focusin'].forEach(e => track.addEventListener(e, hold, { passive: true }));
            track.addEventListener('pointerenter', e => { if (e.pointerType === 'mouse') hovering = true; });
            track.addEventListener('pointerleave', () => { hovering = false; });
            new IntersectionObserver(es => { visible = es[0].isIntersecting; }, { threshold: 0.3 }).observe(track);

            // Auto-slide every 2 seconds. Pauses while the mouse is over a card, resumes when it leaves.
            // The hover check only applies to devices with a real mouse, so touch screens never get stuck paused.
            const canHover = window.matchMedia('(hover: hover) and (pointer: fine)');
            const overCard = () => canHover.matches && cards.some(c => c.matches(':hover'));

            setInterval(() => {
                if (down || animating || document.hidden || overCard()) return;
                const next = cards[best + 1];
                if (next) slideTo(leftFor(next), 900);
            }, 2000);

            const start = () => {
                track.style.scrollSnapType = 'none';
                goTo(n + (best % n), false);
                requestAnimationFrame(() => { track.style.scrollSnapType = ''; paint(); });
            };
            start();
            window.addEventListener('load', start);
            window.addEventListener('resize', start);
        })();

        /* ---------- How it works ---------- */
        const steps = [...document.querySelectorAll('.wp-step')];
        const panes = [...document.querySelectorAll('.wp-pane')];
        const $ = id => document.getElementById(id);
        const note = (el, msg, ok) => { el.textContent = msg; el.classList.toggle('ok', !!ok); };

        function wpGoStep(i) {
            steps.forEach((s, n) => { s.classList.toggle('on', n === i); s.classList.toggle('done', n < i); });
            panes.forEach((p, n) => p.classList.toggle('on', n === i));
        }
        steps.forEach((s, i) => s.addEventListener('click', () => wpGoStep(i)));

        // 1. create account
        $('wpCreate').onclick = () => {
            const name = $('wpN').value.trim();
            note($('wpCreateNote'), `Account created${name ? ' for ' + name : ''}. Next, log in.`, true);
        };

        // 2. log in, open booking
        let loggedIn = false;
        document.querySelectorAll('#wpBar button').forEach(b => b.addEventListener('click', () => {
            const t = b.dataset.t;
            if (t === 'login') {
                loggedIn = true;
                b.textContent = 'Logged in';
                document.querySelector('#wpBar [data-t="booking"]').disabled = false;
                note($('wpBarNote'), 'Logged in. Now open the Booking tab.', true);
            } else if (t === 'booking') {
                document.querySelectorAll('#wpBar button').forEach(x => x.classList.remove('on'));
                b.classList.add('on');
                note($('wpBarNote'), 'You are on the Booking page. Next, choose your rides.', true);
            }
        }));

        // 3. who + rides
        let who = 'Solo';
        const picked = new Set();
        const paintPick = () => {
            const list = [...picked];
            note($('wpPickNote'), list.length ? `${who}: ${list.join(', ')}.` : `${who}, no rides picked yet.`, list.length > 0);
        };
        document.querySelectorAll('#wpWho button').forEach(b => b.addEventListener('click', () => {
            document.querySelectorAll('#wpWho button').forEach(x => x.classList.remove('on'));
            b.classList.add('on'); who = b.textContent; paintPick();
        }));
        document.querySelectorAll('#wpPick button').forEach(b => b.addEventListener('click', () => {
            const v = b.dataset.v;
            picked.has(v) ? picked.delete(v) : picked.add(v);
            b.classList.toggle('on', picked.has(v));
            paintPick();
        }));

        // 4. date, time, QR payment
        const datesEl = $('wpDates');
        for (let i = 0; i < 5; i++) {
            const d = new Date(); d.setDate(d.getDate() + i);
            const b = document.createElement('button');
            b.type = 'button';
            b.textContent = i === 0 ? 'Today' : d.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' });
            if (i === 0) b.className = 'on';
            datesEl.appendChild(b);
        }
        ['wpDates', 'wpTimes'].forEach(id => $(id).addEventListener('click', e => {
            const b = e.target.closest('button'); if (!b) return;
            [...$(id).children].forEach(x => x.classList.remove('on'));
            b.classList.add('on');
        }));

        const qr = $('wpQr');
        let seed = 7;
        const rnd = () => (seed = (seed * 9301 + 49297) % 233280) / 233280;
        const finder = (r, c) => (r < 3 && c < 3) || (r < 3 && c > 7) || (r > 7 && c < 3);
        for (let r = 0; r < 11; r++) for (let c = 0; c < 11; c++) {
            const i = document.createElement('i');
            if (!(finder(r, c) || rnd() > 0.5)) i.className = 'o';
            qr.appendChild(i);
        }
        $('wpPay').onclick = function () {
            $('wpPayRow').hidden = false;
            this.textContent = 'QR shown';
        };

        // 5. proof of payment
        let approved = false, uploading = false;
        const drop = $('wpDrop');
        const upload = () => {
            if (uploading) return;
            uploading = true;
            drop.classList.add('has');
            drop.textContent = '✅ receipt.png attached';
            $('wpBarWrap').hidden = false;
            note($('wpProofNote'), 'Proof sent. Waiting for approval...');
            requestAnimationFrame(() => requestAnimationFrame(() => { $('wpBarFill').style.width = '100%'; }));
            setTimeout(() => {
                approved = true;
                note($('wpProofNote'), 'Payment approved! Your voucher is ready in the next step.', true);
            }, 2300);
        };
        drop.addEventListener('click', upload);
        drop.addEventListener('keydown', e => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); upload(); } });

        // 6. voucher
        $('wpReveal').onclick = () => {
            if (!approved) {
                note($('wpVNote'), 'Your payment is not approved yet. Go back and upload your proof of payment.');
                return;
            }
            const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
            const pick = n => Array.from({ length: n }, () => chars[Math.floor(Math.random() * chars.length)]).join('');
            $('wpCode').textContent = `WP-${pick(4)}-${pick(2)}`;
            $('wpVoucher').classList.remove('locked');
            note($('wpVNote'), 'Show this code to the cashier on site and enjoy your day.', true);
        };

        /* ---------- Visit us: live open status (Philippine time) ---------- */
        (function visitStatus() {
            const fmt = new Intl.DateTimeFormat('en-US', { timeZone: 'Asia/Manila', weekday: 'short', hour: 'numeric', minute: 'numeric', hour12: false });
            const parts = Object.fromEntries(fmt.formatToParts(new Date()).map(p => [p.type, p.value]));
            const dayMap = { Sun: 0, Mon: 1, Tue: 2, Wed: 3, Thu: 4, Fri: 5, Sat: 6 };
            const day = dayMap[parts.weekday];
            const mins = (parseInt(parts.hour, 10) % 24) * 60 + parseInt(parts.minute, 10);
            const openAt = d => (d === 0 || d === 6) ? 10 * 60 : 11 * 60;
            const label = m => { const h = Math.floor(m / 60); return `${((h + 11) % 12) + 1}:00 ${h >= 12 ? 'PM' : 'AM'}`; };
            const CLOSE = 21 * 60;
            const box = document.getElementById('wpStatus');
            const main = document.getElementById('wpStatusMain');
            const sub = document.getElementById('wpStatusSub');

            document.querySelectorAll('.wp-hours .row').forEach(r => {
                if (r.dataset.days.split(',').map(Number).includes(day)) r.classList.add('today');
            });

            if (mins >= openAt(day) && mins < CLOSE) {
                box.classList.add('open');
                main.textContent = 'Open now';
                sub.textContent = `Open until ${label(CLOSE)} today.`;
            } else {
                const upcoming = mins < openAt(day) ? day : (day + 1) % 7;
                main.textContent = 'Closed now';
                sub.textContent = `Opens ${mins < openAt(day) ? 'today' : 'tomorrow'} at ${label(openAt(upcoming))}.`;
            }
        })();

        // Scroll reveal
        const revealEls = document.querySelectorAll('.reveal, .reveal-left, .reveal-right, .stagger');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12 });
        revealEls.forEach(el => observer.observe(el));

        /* ---------- Modal ---------- */
        const wpModalData = {
            'pass-dino': {
                icon: '🦕', tag: 'DinoLakbay',
                tagColor: 'rgba(255,182,39,.18)', tagText: 'var(--amber)',
                title: 'DinoLakbay Services',
                desc: 'Indoor dinosaur playground services. Ask our staff for the current rates.',
                list: [
                    '1 Hour play session',
                    '2 Hours play session',
                    'All Day Pass',
                    'Guardian entry',
                    'Additional 30 minutes',
                    'Additional hour'
                ]
            },
            'pass-roller': {
                icon: '🛼', tag: 'Skataya',
                tagColor: 'rgba(45,212,191,.15)', tagText: 'var(--teal)',
                title: 'Skataya Services',
                desc: 'Roller skating sessions and group bundles. Skates and gear rental are available. Ask our staff for the current rates.',
                list: [
                    'Regular, 1 Hour',
                    'Regular, 2 Hours',
                    'Regular, All Day Pass',
                    'Socks',
                    'Group Bundle (4+1), 1 Hour',
                    'Group Bundle (4+1), 2 Hours',
                    'Skates and gear rental',
                    'Birthdays, group parties and company events: inquire inside'
                ]
            },
            'pass-fields': {
                icon: '🎡', tag: 'PiyestaRides',
                tagColor: 'rgba(255,107,107,.15)', tagText: 'var(--coral)',
                title: 'PiyestaRides: Rides Offered',
                desc: 'Rides you can enjoy in the PiyestaRides zone. Promo bundles may be available seasonally.',
                list: [
                    'Tiger Train',
                    'Mini Carousel',
                    'Star Speed',
                    'Little Chicken',
                    'Boat Pool',
                    'Carousel',
                    'Flying Chair',
                    'Mini Ferris Wheel',
                    'Samba Balloon',
                    'Crazy Plane',
                    'Vikings',
                    'Go-Kart',
                    'Inflatable Playground',
                    'Mini Trampoline',
                    'Rev & Roll',
                    'Happy Cars',
                    'Jurassic Adventure'
                ]
            },
            'party': {
                icon: '🎉', tag: 'Guest Services',
                tagColor: 'rgba(255,107,107,.15)', tagText: 'var(--coral)',
                title: 'Birthday & Party Packages',
                desc: 'Hold a birthday, school outing or team day at Pistavora. Packages can mix all three zones to suit your group\'s age and interests.',
                list: [
                    'Available at PiyestaRides, DinoLakbay or Skataya',
                    'Options from small family parties to large group events',
                    'Good for birthdays, school field trips and team building',
                    'Arranged in advance with our reservations team',
                    'Ask staff about add-ons like reserved seating or the Skate & Celebrate photo corner'
                ]
            },
            'reservation': {
                icon: '📝', tag: 'Guest Services',
                tagColor: 'rgba(255,182,39,.18)', tagText: 'var(--amber)',
                title: 'Reservation & Confirmation',
                desc: 'Send your preferred date, zone and headcount, and our team handles the rest.',
                list: [
                    'Submit your booking request with event details online',
                    'Reservations depend on slot availability',
                    'Our team reviews and confirms each request',
                    'You get a confirmation once your slot is secured',
                    'Book group and school visits in advance'
                ]
            },
            'channels': {
                icon: '🌐', tag: 'Guest Services',
                tagColor: 'rgba(45,212,191,.15)', tagText: 'var(--teal)',
                title: 'Multiple Booking Channels',
                desc: 'Book directly with Pistavora or through one of our partners, whichever is easier for you.',
                list: [
                    'Book on the Pistavora website for the most flexibility',
                    'Book through Klook for select attractions and promos',
                    'Book through StarDeals for seasonal discounts and vouchers',
                    'Voucher bookings are redeemed at the gate',
                    'Rates and inclusions may vary slightly by channel'
                ]
            },
            'payment': {
                icon: '💳', tag: 'Guest Services',
                tagColor: 'rgba(139,92,246,.15)', tagText: '#8b5cf6',
                title: 'Flexible Payment Options',
                desc: 'Pay the way that suits you, online or at the gate.',
                list: [
                    'Cash at the gate and on-site counters',
                    'Credit and debit cards',
                    'GCash and Maya e-wallets',
                    'Klook and StarDeals vouchers',
                    'Online payments are confirmed right at checkout'
                ]
            },
            'snacks': {
                icon: '🍿', tag: 'Guest Services',
                tagColor: 'rgba(255,107,107,.15)', tagText: 'var(--coral)',
                title: 'Snack Bar & Refreshments',
                desc: 'Snacks and drinks are sold inside the venue, so you can stay all day.',
                list: [
                    'Snacks, drinks and light meals on site',
                    'Located inside the venue',
                    'Open during regular park hours',
                    'A good stop between zones or during a party'
                ]
            },
            'celebrate': {
                icon: '🎉', tag: 'Party Add-on',
                tagColor: 'rgba(45,212,191,.15)', tagText: 'var(--teal)',
                title: 'Celebrate at Pistavora',
                desc: 'Skataya and DinoLakbay both work as party venues. Pick the one that suits your celebration and our team sets it up.',
                list: [
                    'Skataya: neon photo-booth corner, great for skate parties',
                    'DinoLakbay: giant ball pit inside the dinosaur playhouse',
                    'Good for birthdays, kiddie parties and group celebrations',
                    'Open to all ages, and you can mix zones for bigger groups',
                    'Book ahead with our reservations team to hold your date'
                ]
            },
        };

        function openWpModal(key) {
            const data = wpModalData[key];
            if (!data) return;

            document.getElementById('wpModalIcon').textContent = data.icon;
            const tagEl = document.getElementById('wpModalTag');
            tagEl.textContent = data.tag;
            tagEl.style.background = data.tagColor;
            tagEl.style.color = data.tagText;
            document.getElementById('wpModalTitle').textContent = data.title;
            document.getElementById('wpModalDesc').textContent = data.desc;

            const listEl = document.getElementById('wpModalList');
            listEl.innerHTML = '';
            data.list.forEach(item => {
                const li = document.createElement('li');
                li.textContent = item;
                listEl.appendChild(li);
            });

            document.getElementById('wpModalOverlay').classList.add('open');
            document.body.style.overflow = 'hidden';
        }

        function closeWpModal() {
            document.getElementById('wpModalOverlay').classList.remove('open');
            document.body.style.overflow = '';
        }

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                if (!aboutOverlay.hidden) closeAbout();
                closeWpModal();
            }
        });
    </script>

</body>

</html>