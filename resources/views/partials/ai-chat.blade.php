{{-- কৃষি AI সহকারী — floating chatbot (shown on all authenticated/farmer pages) --}}
<style>
    #ai-fab { position: fixed; right: 16px; bottom: calc(60px + 16px); z-index: 1100; display: inline-flex; align-items: center; gap: 7px;
        background: var(--green-500); color: #fff; border-radius: 999px; padding: 11px 16px; font-size: 14px; font-weight: 600;
        box-shadow: 0 6px 18px rgba(11,107,46,.35); border: none; font-family: 'Hind Siliguri', sans-serif; }
    #ai-fab:hover { background: var(--green-600); }
    #ai-fab .dot { width: 8px; height: 8px; border-radius: 50%; background: #8affb0; box-shadow: 0 0 0 0 rgba(138,255,176,.7); animation: aiPulse 1.8s infinite; }
    @keyframes aiPulse { 0% { box-shadow: 0 0 0 0 rgba(138,255,176,.6);} 70%{ box-shadow:0 0 0 7px rgba(138,255,176,0);} 100%{box-shadow:0 0 0 0 rgba(138,255,176,0);} }

    #ai-panel { position: fixed; right: 16px; bottom: calc(60px + 16px); z-index: 1101; width: min(380px, 94vw); height: min(72vh, 540px);
        background: var(--card); border: 1px solid var(--border); border-radius: var(--radius-lg); box-shadow: 0 12px 40px rgba(0,0,0,.22);
        display: none; flex-direction: column; overflow: hidden; }
    #ai-panel.open { display: flex; }
    #ai-head { background: var(--green-600); color: #fff; padding: 12px 14px; display: flex; align-items: center; gap: 8px; }
    #ai-head h4 { font-family: 'Noto Serif Bengali', serif; font-size: 15px; font-weight: 600; flex: 1; }
    #ai-head button { background: rgba(255,255,255,.18); color: #fff; border: none; width: 28px; height: 28px; border-radius: 7px; font-size: 16px; line-height: 1; }
    #ai-body { flex: 1; overflow-y: auto; padding: 12px; background: var(--bg); display: flex; flex-direction: column; gap: 9px; }
    .ai-msg { max-width: 86%; padding: 9px 12px; border-radius: 12px; font-size: 14px; line-height: 1.55; white-space: pre-wrap; word-break: break-word; }
    .ai-bot { align-self: flex-start; background: var(--card); border: 1px solid var(--border); border-bottom-left-radius: 3px; color: var(--text-primary); }
    .ai-user { align-self: flex-end; background: var(--green-500); color: #fff; border-bottom-right-radius: 3px; }
    .ai-err { align-self: flex-start; background: var(--red-50); border: 1px solid var(--red-100); color: var(--red-500); }
    .ai-typing { align-self: flex-start; display: inline-flex; gap: 4px; padding: 11px 13px; background: var(--card); border: 1px solid var(--border); border-radius: 12px; }
    .ai-typing span { width: 6px; height: 6px; border-radius: 50%; background: var(--text-muted); animation: aiBlink 1.2s infinite both; }
    .ai-typing span:nth-child(2){animation-delay:.2s} .ai-typing span:nth-child(3){animation-delay:.4s}
    @keyframes aiBlink { 0%,80%,100%{opacity:.25} 40%{opacity:1} }
    #ai-suggest { padding: 8px 12px; display: flex; flex-wrap: wrap; gap: 6px; border-top: 1px solid var(--border); background: var(--card); }
    #ai-suggest button { background: var(--green-50); color: var(--green-600); border: 1px solid var(--green-100); border-radius: 999px; padding: 6px 10px; font-size: 12px; font-family: 'Hind Siliguri', sans-serif; text-align: right; }
    #ai-foot { display: flex; gap: 7px; padding: 10px; border-top: 1px solid var(--border); background: var(--card); }
    #ai-input { flex: 1; resize: none; border: 1.5px solid var(--border); border-radius: 10px; padding: 9px 11px; font-size: 14px; max-height: 90px; font-family: 'Hind Siliguri', sans-serif; }
    #ai-send { background: var(--green-500); color: #fff; border: none; border-radius: 10px; padding: 0 16px; font-weight: 600; }
    #ai-send:disabled { opacity: .5; }
    #ai-foot .hint { font-size: 10px; color: var(--text-muted); }
</style>

<button id="ai-fab" type="button" onclick="aiToggle(true)"><span class="dot"></span>🤖 কৃষি AI</button>

<div id="ai-panel" aria-live="polite">
    <div id="ai-head">
        <span style="font-size:18px;">🤖</span>
        <h4>কৃষি AI সহকারী</h4>
        <button type="button" title="ছোট করুন" onclick="aiToggle(false)">—</button>
    </div>
    <div id="ai-body">
        <div class="ai-msg ai-bot">আসসালামু আলাইকুম! 👋 আমি কৃষি-বন্ধুর AI সহকারী। ফসল, সার, বীজ, কীটনাশক, সেচ বা বাজার দর সম্পর্কে আপনার প্রশ্ন লিখুন — আমি সহজ বাংলায় উত্তর দেব।</div>
    </div>
    <div id="ai-suggest">
        @foreach([
            'ধানের পাতা হলুদ হলে কী করবো?',
            'টমেটো গাছে পোকা হলে কী ব্যবহার করবো?',
            'বৃষ্টির আগে ইউরিয়া সার দেওয়া যাবে?',
            'ধানের বাজার দর কোথায় দেখবো?',
            'ট্রাক্টর ভাড়া কোথায় পাবো?',
        ] as $q)
            <button type="button" onclick="aiAsk(@js($q))">{{ $q }}</button>
        @endforeach
    </div>
    <div id="ai-foot">
        <textarea id="ai-input" rows="1" maxlength="500" placeholder="আপনার প্রশ্ন লিখুন..." onkeydown="aiKey(event)"></textarea>
        <button id="ai-send" type="button" onclick="aiAsk()">পাঠান</button>
    </div>
</div>

<script>
    (function () {
        var URL = @js(route('chat.send'));
        var CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        var panel = document.getElementById('ai-panel');
        var fab = document.getElementById('ai-fab');
        var body = document.getElementById('ai-body');
        var input = document.getElementById('ai-input');
        var sendBtn = document.getElementById('ai-send');
        var suggest = document.getElementById('ai-suggest');
        var busy = false;

        window.aiToggle = function (open) {
            panel.classList.toggle('open', open);
            fab.style.display = open ? 'none' : 'inline-flex';
            if (open) setTimeout(function () { input.focus(); }, 50);
        };

        function bubble(text, cls) {
            var d = document.createElement('div');
            d.className = 'ai-msg ' + cls;
            d.textContent = text;
            body.appendChild(d);
            body.scrollTop = body.scrollHeight;
            return d;
        }

        function typing() {
            var t = document.createElement('div');
            t.className = 'ai-typing';
            t.innerHTML = '<span></span><span></span><span></span>';
            body.appendChild(t);
            body.scrollTop = body.scrollHeight;
            return t;
        }

        window.aiKey = function (e) {
            if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); window.aiAsk(); }
        };

        window.aiAsk = function (preset) {
            if (busy) return;
            var msg = (preset || input.value || '').trim();
            if (!msg) return;
            if (suggest) suggest.style.display = 'none';
            bubble(msg, 'ai-user');
            input.value = '';
            busy = true; sendBtn.disabled = true;
            var t = typing();

            fetch(URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                body: JSON.stringify({ message: msg })
            }).then(function (r) {
                return r.json().then(function (data) { return { ok: r.ok, status: r.status, data: data }; });
            }).then(function (res) {
                t.remove();
                if (res.ok && res.data.answer) {
                    bubble(res.data.answer, 'ai-bot');
                } else {
                    bubble(res.data.message || 'দুঃখিত, এখন উত্তর দেওয়া যাচ্ছে না। কিছুক্ষণ পরে আবার চেষ্টা করুন।', 'ai-err');
                }
            }).catch(function () {
                t.remove();
                bubble('দুঃখিত, এখন উত্তর দেওয়া যাচ্ছে না। কিছুক্ষণ পরে আবার চেষ্টা করুন।', 'ai-err');
            }).finally(function () {
                busy = false; sendBtn.disabled = false;
            });
        };

        // Auto-grow textarea
        input.addEventListener('input', function () {
            input.style.height = 'auto';
            input.style.height = Math.min(input.scrollHeight, 90) + 'px';
        });
    })();
</script>
