(function () {
  const messagesEl = document.getElementById('chatMessages');
  const inputEl = document.getElementById('chatInput');
  const sendBtn = document.getElementById('chatSend');

  let history = [];

  function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.content : null;
  }

  const WONDER_PARK_AVATAR_IMG = `<img src="/images/pistavora-mark-dark.svg" alt="Pistavora">`;

  function appendMessage(text, sender) {
    const row = document.createElement('div');
    row.className = 'chat-row ' + (sender === 'user' ? 'user' : 'bot');

    if (sender !== 'user') {
      const avatar = document.createElement('span');
      avatar.className = 'chat-avatar';
      avatar.innerHTML = WONDER_PARK_AVATAR_IMG;
      row.appendChild(avatar);
    }

    const bubble = document.createElement('div');
    bubble.className = 'bubble ' + (sender === 'user' ? 'user' : 'bot');
    bubble.textContent = text;
    bubble.style.whiteSpace = 'pre-line';
    row.appendChild(bubble);

    messagesEl.appendChild(row);
    messagesEl.scrollTop = messagesEl.scrollHeight;
  }

  function appendTyping() {
    const row = document.createElement('div');
    row.className = 'chat-row bot';
    row.id = 'typingIndicator';

    const avatar = document.createElement('span');
    avatar.className = 'chat-avatar';
    avatar.innerHTML = WONDER_PARK_AVATAR_IMG;
    row.appendChild(avatar);

    const bubble = document.createElement('div');
    bubble.className = 'bubble bot';
    bubble.textContent = 'Typing...';
    row.appendChild(bubble);

    messagesEl.appendChild(row);
    messagesEl.scrollTop = messagesEl.scrollHeight;
  }

  function removeTyping() {
    const el = document.getElementById('typingIndicator');
    if (el) el.remove();
  }

  let sending = false;

  async function sendMessage(text) {
    if (!text || !text.trim() || sending) return;
    sending = true;

    appendMessage(text, 'user');
    inputEl.value = '';
    appendTyping();

    try {
      const res = await fetch('/api/chat/message', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': getCsrfToken() || '',
          'Accept': 'application/json',
        },
        body: JSON.stringify({ message: text, history: history }),
      });

      let data = null;
      try { data = await res.json(); } catch (e) { data = null; }
      removeTyping();
      if (!data || typeof data.reply !== 'string' || !data.reply.trim()) {
        appendMessage("Sorry, I ran into a glitch. Please try again in a moment.", 'bot');
        sending = false;
        return;
      }
      appendMessage(data.reply, 'bot');

      // Don't save failed replies (glitch / busy messages) as if the bot
      // had said them — they'd confuse the next answer.
      if (!data.error) {
        history.push({ role: 'user', content: text });
        history.push({ role: 'assistant', content: data.reply });
      }
    } catch (err) {
      removeTyping();
      appendMessage("Sorry, I can't reach the server right now. Please try again in a moment.", 'bot');
      console.error('Pistavora chat error:', err);
    } finally {
      sending = false;
    }
  }

  sendBtn?.addEventListener('click', () => sendMessage(inputEl.value));
  inputEl?.addEventListener('keydown', (e) => {
    if (e.key === 'Enter') sendMessage(inputEl.value);
  });

  document.querySelectorAll('.quick[data-msg]').forEach((el) => {
    el.addEventListener('click', () => sendMessage(el.dataset.msg));
  });
})();
