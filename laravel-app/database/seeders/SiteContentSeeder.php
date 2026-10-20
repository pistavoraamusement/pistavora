<?php

namespace Database\Seeders;

use App\Models\SiteCard;
use App\Models\SiteContent;
use Illuminate\Database\Seeder;

class SiteContentSeeder extends Seeder
{
    public function run(): void
    {
        // ---- HERO ----
        SiteContent::set('hero', 'title', 'Three worlds of thrill,<br><span class="accent">one ticket away.</span>');
        SiteContent::set('hero', 'description', "From the gravity-defying loops of Skataya to the prehistoric trails of DinoLakbay, Pistavora is built for a full day of family fun — now bookable, trackable, and paid for online.");
        SiteContent::set('hero', 'price_teaser', '₱149');
        SiteContent::set('hero', 'video_path', '/videos/landing.mp4');

        // ---- SPLIT SECTION ----
        SiteContent::set('split', 'tag', 'Plan Your Day');
        SiteContent::set('split', 'title', 'Make it a full day out — not just a stop-by');
        SiteContent::set('split', 'description', "Pistavora sits inside the Lima Technology Center complex, so a booking here pairs easily with a longer family day: grab a meal nearby, then swing through all three zones before closing.");
        SiteContent::set('split', 'location_tag', 'Lima Technology Center · Lipa City / Malvar');

        // ---- CONTACT ----
        SiteContent::set('contact', 'location', 'Lima Technology Center, Lipa City/Malvar, Batangas');
        SiteContent::set('contact', 'hours_weekend', '10:00 AM – 9:00 PM Weekends');
        SiteContent::set('contact', 'hours_weekday', '11:00 AM – 9:00 PM Weekdays');
        SiteContent::set('contact', 'zones', 'PiyestaRides · Skataya · DinoLakbay');

        // ---- FOOTER ----
        SiteContent::set('footer', 'tagline', 'From thrilling rides to prehistoric adventures and endless skating fun, Pistavora is your destination for unforgettable family experiences.');

        // ---- PASSES ----
        SiteCard::updateOrCreate(['slug' => 'pass-dino'], [
            'type' => 'pass', 'badge_label' => 'DinoLakbay', 'title' => 'Dino Day Pass',
            'price_display' => '₱599', 'button_text' => 'Book Now', 'sort_order' => 1,
            'features' => [
                'Unlimited all-day access',
                'Includes 1 child + 1 guardian',
                'Access to all DinoLakbay play areas',
                'Perfect for ages 4–12',
            ],
            'modal_list' => [
                '• 1 Hour — ₱299', '• 2 Hours — ₱399', '• All Day Pass — ₱599',
                '• Guardian entry — ₱50', '• Additional 30 mins — ₱149', '• Additional hour — ₱199',
            ],
        ]);

        SiteCard::updateOrCreate(['slug' => 'pass-roller'], [
            'type' => 'pass', 'badge_label' => 'Most Popular', 'title' => 'Skataya Pass',
            'price_display' => '₱599', 'button_text' => 'Book Now', 'is_featured' => true, 'sort_order' => 2,
            'features' => [
                'Unlimited all-day skating access',
                'Open for kids, teens, and adults',
                'Great for families and groups',
                'Skate rental included',
            ],
            'modal_list' => [
                '• Regular — 1 Hour: ₱249', '• Regular — 2 Hours: ₱399', '• Regular — All Day Pass: ₱599',
                '• Socks — ₱50', '• Group Bundle (4+1) — 1 Hour: ₱996', '• Group Bundle (4+1) — 2 Hours: ₱1,596',
                '• Skates and Gears rental — ₱50', '• Birthday, group parties & company events — inquire inside',
            ],
        ]);

        SiteCard::updateOrCreate(['slug' => 'pass-fields'], [
            'type' => 'pass', 'badge_label' => 'PiyestaRides', 'title' => 'Ride-All-You-Can',
            'price_display' => 'Promo', 'button_text' => 'View Promos', 'sort_order' => 3,
            'features' => [
                'Ride-all-you-can packages available',
                'Special discounts via Klook & StarDeals',
                'Seasonal and event-based promotions',
                'Ask our staff for current rates',
            ],
            'modal_list' => [
                '• Tiger Train — ₱60 per head', '• Mini Carousel — ₱60 per head', '• Star Speed — ₱60 per head',
                '• Little Chicken — ₱60 per head', '• Boat Pool — ₱60 per head', '• Carousel — ₱60 per head',
                '• Flying Chair — ₱60 per head', '• Mini Ferris Wheel — ₱60 per head', '• Samba Balloon — ₱60 per head',
                '• Crazy Plane — ₱60 per head', '• Vikings — ₱120 per head', '• Go-Kart — ₱120 per head',
                '• Inflatable Playground — ₱150 per head', '• Mini Trampoline — ₱150 per 30 mins',
                '• Rev & Roll — ₱150 per car', '• Happy Cars — ₱150 per car', '• Jurassic Adventure — ₱150 per ride',
            ],
        ]);

        // ---- ATTRACTIONS ----
        SiteCard::updateOrCreate(['slug' => 'attraction-field-of-rides'], [
            'type' => 'attraction', 'badge_label' => 'PiyestaRides', 'icon' => 'coral',
            'title' => 'PiyestaRides', 'sort_order' => 1,
            'description' => 'Enjoy exciting amusement rides ranging from family attractions to high-thrill experiences designed for adventure seekers.',
            'features' => ['Multiple Attractions', 'Ride-Specific Rules'],
            'modal_list' => [
                "Ride eligibility depends on each attraction's height and safety requirements.",
                'Guests with heart conditions, severe asthma, epilepsy, recent injuries, or similar medical concerns should avoid extreme rides.',
                'Pregnant guests are not permitted on extreme or high-impact attractions.',
                'Guests below 18 years old require parent or guardian consent.',
            ],
        ]);

        SiteCard::updateOrCreate(['slug' => 'attraction-roller-fever'], [
            'type' => 'attraction', 'badge_label' => 'Skataya', 'icon' => 'teal',
            'title' => 'Skataya Skating Rink', 'sort_order' => 2,
            'description' => 'Experience all-day roller skating fun in a safe and family-friendly environment. Perfect for beginners and experienced skaters alike.',
            'features' => ['All-Day Access', 'Family Friendly'],
            'modal_list' => [
                'Children aged 4–6 years old must be accompanied by a guardian inside the skating area.',
                'Guardians entering the rink must also wear roller skates.',
                'Guests below 18 years old require parent or guardian consent.',
                'Consent may be provided in person or through call, text message, or online chat confirmation.',
            ],
        ]);

        SiteCard::updateOrCreate(['slug' => 'attraction-dino-adventure'], [
            'type' => 'attraction', 'badge_label' => 'DinoLakbay', 'icon' => 'amber',
            'title' => 'DinoLakbay Playground', 'sort_order' => 3,
            'description' => 'Explore a prehistoric-themed indoor playground featuring slides, climbing areas, obstacle courses, and interactive play zones.',
            'features' => ['Max Age: 12', 'Indoor Playground'],
            'modal_list' => [
                'Children aged 1–5 years old must always be accompanied by a guardian.',
                'Only children 12 years old and below are allowed to use the play facilities.',
                'Guardians are responsible for supervising young children at all times.',
                'Guests below 18 years old require parent or guardian consent.',
            ],
        ]);

        SiteCard::updateOrCreate(['slug' => 'celebrate'], [
            'type' => 'attraction', 'badge_label' => 'Skataya', 'icon' => 'teal',
            'title' => 'Celebrate at Pistavora', 'sort_order' => 4, 'button_link' => '#',
            'description' => 'Both Skataya and DinoLakbay welcome birthdays and group celebrations — skate under the neon photo-booth corner or gather the kids around the giant ball pit inside the prehistoric playhouse.',
            'features' => ['All ages', 'Party Add-on'],
        ]);

        // ---- GUEST SERVICES ----
        SiteCard::updateOrCreate(['slug' => 'party'], [
            'type' => 'service', 'icon' => '🎉', 'title' => 'Birthday & Party Packages', 'sort_order' => 1,
            'description' => 'Celebrate special occasions at PiyestaRides, DinoLakbay, or Skataya. Available for birthdays, school groups, and team-building events.',
            'badge_label' => 'Guest Services',
            'modal_list' => [
                '• Available at PiyestaRides, DinoLakbay, or Skataya',
                '• Options for small family celebrations up to large group events',
                '• Suited for birthdays, school field trips, and team-building activities',
                '• Coordinated in advance with our reservations team',
                '• Ask staff about add-ons like reserved seating or the Skate & Celebrate photo corner',
            ],
        ]);

        SiteCard::updateOrCreate(['slug' => 'reservation'], [
            'type' => 'service', 'icon' => '📝', 'title' => 'Reservation & Confirmation', 'sort_order' => 2,
            'description' => 'Submit your booking request and event details. Reservations are subject to availability and confirmed by our team after review.',
            'badge_label' => 'Guest Services',
            'modal_list' => [
                '• Submit your booking request with event details online',
                '• Reservations are subject to slot availability',
                '• Our team reviews and confirms each request',
                '• Confirmation is sent once your slot is secured',
                '• Group and school bookings should be made in advance',
            ],
        ]);

        SiteCard::updateOrCreate(['slug' => 'channels'], [
            'type' => 'service', 'icon' => '🌐', 'title' => 'Multiple Booking Channels', 'sort_order' => 3,
            'description' => 'Book directly with Pistavora, or through trusted partners including Klook and StarDeals for selected attractions and promos.',
            'badge_label' => 'Guest Services',
            'modal_list' => [
                '• Book directly on the Pistavora website for full flexibility',
                '• Book through Klook for select attractions and promos',
                '• Book through StarDeals for seasonal discounts and vouchers',
                '• Voucher-based bookings are redeemed at the gate',
                '• Rates and inclusions may vary slightly by channel',
            ],
        ]);

        SiteCard::updateOrCreate(['slug' => 'payment'], [
            'type' => 'service', 'icon' => '💳', 'title' => 'Flexible Payment Options', 'sort_order' => 4,
            'description' => 'We accept Cash, Credit Card, Debit Card, GCash, Maya, Klook vouchers, and StarDeals vouchers.',
            'badge_label' => 'Guest Services',
            'modal_list' => [
                '• Cash — accepted at the gate and on-site counters',
                '• Credit Card & Debit Card',
                '• GCash and Maya e-wallets',
                '• Klook vouchers and StarDeals vouchers',
                '• Online payments are confirmed instantly upon checkout',
            ],
        ]);

        SiteCard::updateOrCreate(['slug' => 'snacks'], [
            'type' => 'service', 'icon' => '🍿', 'title' => 'Snack Bar & Refreshments', 'sort_order' => 5,
            'description' => 'Snacks, drinks, and refreshments available inside the venue so guests stay energized all day.',
            'badge_label' => 'Guest Services',
            'modal_list' => [
                '• Snacks, drinks, and light meals available on-site',
                '• Conveniently located within the venue',
                '• Open throughout regular park hours',
                '• Great stop between zones or during party bookings',
            ],
        ]);

        // ---- HOW IT WORKS ----
        SiteCard::updateOrCreate(['slug' => 'step-book'], [
            'type' => 'step', 'icon' => '1️⃣', 'title' => 'Book online', 'sort_order' => 1,
            'badge_label' => 'How it works',
            'description' => 'Choose your date, zone, and headcount.',
            'modal_list' => [
                '• Pick your preferred date and time',
                '• Choose your zone: PiyestaRides, Skataya, or DinoLakbay',
                '• Enter your headcount, including children and guardians',
                '• Receive an e-ticket once your booking is confirmed',
                '• Walk-ins are welcome too, subject to capacity',
            ],
        ]);

        SiteCard::updateOrCreate(['slug' => 'step-scan'], [
            'type' => 'step', 'icon' => '2️⃣', 'title' => 'Scan at the gate', 'sort_order' => 2,
            'badge_label' => 'How it works',
            'description' => 'Show your e-ticket — POS verifies it instantly.',
            'modal_list' => [
                '• Present your e-ticket QR code at the entrance',
                '• Staff scans and validates it on the spot',
                '• Guests below 18 need parent or guardian consent',
                '• Consent can be given in person, by call, text, or online chat',
                '• Keep your ticket handy in case re-verification is needed',
            ],
        ]);

        SiteCard::updateOrCreate(['slug' => 'step-ride'], [
            'type' => 'step', 'icon' => '3️⃣', 'title' => 'Ride all day', 'sort_order' => 3,
            'badge_label' => 'How it works',
            'description' => 'Enjoy PiyestaRides, Skataya, and DinoLakbay access.',
            'modal_list' => [
                '• Enjoy unlimited access within your booked zone',
                "• Ride eligibility follows each attraction's height and safety rules",
                '• Guests with certain health conditions should avoid extreme rides',
                '• Guardians must accompany young children per zone-specific rules',
                '• Valid until park closing time on your visit date',
            ],
        ]);
    }
}