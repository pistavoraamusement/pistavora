{{--
    resources/views/partials/chat-widget.blade.php
    Floating "Pistavora" chat button + panel, shared across every user page.
    Wired up to ChatController via public/js/reks-assist.js
--}}
<div class="chat-window" id="chatWindow">
    <div class="chat-head">
        <div>
            Pistavora
            <small>AI chatbot</small>
        </div>
        <span style="cursor:pointer;" onclick="document.getElementById('chatWindow').classList.remove('open')">&times;</span>
    </div>
    <div class="chat-body" id="chatMessages">
        <div class="chat-row bot">
            <span class="chat-avatar">
                <img src="{{ asset('images/pistavora-mark-dark.svg') }}" alt="Pistavora">
            </span>
            <div class="bubble bot">
                Hi! I'm the Pistavora assistant 👋 Ask me about our packages, pricing, or how booking works — happy to help you plan your visit!
            </div>
        </div>
        <div id="chatQuickActions">
            <span class="quick" data-msg="What's included in DinoLakbay?">What's included in DinoLakbay?</span>
            <span class="quick" data-msg="How much is Skataya?">How much is Skataya?</span>
            <span class="quick" data-msg="How much is PiyestaRides?">How much is PiyestaRides?</span>
        </div>
    </div>
    <div class="chat-input">
        <input type="text" id="chatInput" placeholder="Type a message...">
        <button type="button" id="chatSend">&#10148;</button>
    </div>
</div>
<button class="chat-fab" onclick="document.getElementById('chatWindow').classList.add('open')">&#128172;</button>

<script src="{{ asset('js/reks-assist.js') }}?v={{ filemtime(public_path('js/reks-assist.js')) }}"></script>
