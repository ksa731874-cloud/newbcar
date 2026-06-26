<?php
// chat_widget.php - Include this in all pages
if (isset($User) && $User->getSetting('chat_enabled') === '0') {
    return;
}
?>
<!-- FontAwesome & Pusher JS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>

<style>
    /* Premium Hover FAB */
    #chat-fab {
        position: fixed;
        bottom: 25px;
        left: 25px;
        width: 62px;
        height: 62px;
        background: linear-gradient(135deg, #156394, #2a88c4);
        color: #fff;
        border-radius: 50%;
        display: flex;
        justify-content: center;
        align-items: center;
        box-shadow: 0 8px 25px rgba(21, 99, 148, 0.4);
        cursor: pointer;
        z-index: 999999;
        transition: all 0.4s cubic-bezier(0.68, -0.55, 0.27, 1.55);
        border: 2px solid rgba(255,255,255,0.1);
    }

    #chat-fab:hover {
        transform: scale(1.12) translateY(-5px);
        box-shadow: 0 12px 30px rgba(21, 99, 148, 0.5);
    }

    #chat-fab i {
        font-size: 1.6rem;
    }

    #chat-fab-badge {
        position: absolute;
        top: -4px;
        left: -4px;
        min-width: 22px;
        height: 22px;
        padding: 0 6px;
        border-radius: 50%;
        background: #ef4444;
        color: white;
        font-size: 11px;
        font-weight: 800;
        display: none;
        align-items: center;
        justify-content: center;
        border: 2px solid white;
        box-shadow: 0 4px 10px rgba(239, 68, 68, 0.4);
        line-height: 1;
    }

    @keyframes fabBadgePulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.4); }
    }
    #chat-fab-badge.pulse { animation: fabBadgePulse 0.4s ease-out; }

    @keyframes fabNotification {
        0%, 100% { transform: rotate(0); }
        25% { transform: rotate(15deg); }
        75% { transform: rotate(-15deg); }
    }
    #chat-fab.shake { animation: fabNotification 0.5s ease-in-out infinite; }

    #chat-box {
        display: none;
        position: fixed;
        bottom: 100px;
        left: 25px;
        width: 380px;
        max-width: 92vw;
        height: 550px;
        max-height: 75vh;
        background: #fff;
        z-index: 999999;
        flex-direction: column;
        border-radius: 20px;
        box-shadow: 0 15px 50px rgba(0, 0, 0, 0.18);
        overflow: hidden;
        border: 1px solid rgba(0,0,0,0.05);
        animation: chatOpenAnim 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
    }

    @keyframes chatOpenAnim {
        from { opacity: 0; transform: translateY(30px) scale(0.95); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }

    #chat-header {
        background: #156394;
        padding: 18px 20px;
        color: #fff;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .chat-status {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.95rem;
    }

    .status-dot {
        width: 8px;
        height: 8px;
        background: #34d399;
        border-radius: 50%;
        box-shadow: 0 0 8px #34d399;
    }

    #chat-messages {
        flex: 1;
        overflow-y: auto;
        padding: 20px;
        background: #f8fafc;
        display: flex;
        flex-direction: column;
        gap: 15px;
        scroll-behavior: smooth;
    }

    .chat-msg {
        max-width: 85%;
        padding: 12px 16px;
        font-size: 14px;
        line-height: 1.6;
        position: relative;
    }

    .chat-msg-visitor {
        align-self: flex-end;
        background: #156394;
        color: #fff;
        border-radius: 18px 18px 2px 18px;
        filter: drop-shadow(0 2px 4px rgba(21, 99, 148, 0.1));
    }

    .chat-msg-admin {
        align-self: flex-start;
        background: #fff;
        color: #1e293b;
        border-radius: 18px 18px 18px 2px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.04);
        border: 1px solid #f1f5f9;
        font-weight: 500;
    }

    .msg-time {
        font-size: 10px;
        opacity: 0.6;
        margin-top: 5px;
    }

    #typing-indicator {
        display: none;
        align-self: flex-start;
        font-size: 12px;
        color: #64748b;
        background: #fff;
        padding: 8px 15px;
        border-radius: 10px;
        margin-bottom: 5px;
    }

    #chat-input-area {
        padding: 15px 20px;
        background: #fff;
        border-top: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    #chat-input {
        flex: 1;
        border: none;
        background: #f1f5f9;
        border-radius: 30px;
        padding: 10px 18px;
        font-size: 14px;
        outline: none;
        transition: background 0.2s;
    }

    #chat-input:focus {
        background: #e2e8f0;
    }

    #chat-send-btn {
        background: #156394;
        color: #fff;
        border: none;
        width: 42px;
        height: 42px;
        border-radius: 50%;
        display: flex;
        justify-content: center;
        align-items: center;
        cursor: pointer;
        transition: transform 0.2s, background 0.2s;
        box-shadow: 0 4px 10px rgba(21,99,148,0.2);
    }

    #chat-send-btn:hover { background: #1a71ab; transform: scale(1.05); }
    #chat-send-btn:active { transform: scale(0.95); }

    .close-btn {
        cursor: pointer;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        transition: background 0.2s;
    }
    .close-btn:hover { background: rgba(255,255,255,0.15); }
</style>

<div id="chat-fab">
    <i class="fa-solid fa-message"></i>
    <div id="chat-fab-badge">0</div>
</div>

<div id="chat-box">
    <div id="chat-header">
        <div class="chat-status">
            <div class="status-dot"></div>
            <span>خدمة العملاء</span>
        </div>
        <div class="close-btn" onclick="toggleChatWindow()">
            <i class="fa-solid fa-times"></i>
        </div>
    </div>

    <div id="chat-messages"></div>

    <div id="typing-indicator">جاري الكتابة...</div>

    <form id="chat-input-area">
        <input type="text" id="chat-input" placeholder="اكتب طلبك هنا..." autocomplete="off">
        <button type="submit" id="chat-send-btn">
            <i class="fa-solid fa-paper-plane fa-flip-horizontal"></i>
        </button>
    </form>
</div>

<script>
    (function () {
        const chatSessionId = localStorage.getItem('chat_session_id') || ('sess_' + Math.random().toString(36).substr(2, 12));
        localStorage.setItem('chat_session_id', chatSessionId);

        const blockedWordsStr = "<?= isset($User) ? addslashes($User->getSetting('blocked_words')) : '' ?>";
        const blockedWords = blockedWordsStr ? blockedWordsStr.split(',').map(s => s.trim().toLowerCase()).filter(s => s) : [];

        const isDashboard = window.location.pathname.includes('/dashboard/');
        const handlerPath = isDashboard ? '../pusher_chat_handler.php' : 'pusher_chat_handler.php';
        const historyPath = isDashboard ? '../chat_history.php' : 'chat_history.php';

        let isChatOpen = false;
        let unreadCount = 0;

        function updateFabBadge() {
            const fabBadge = document.getElementById('chat-fab-badge');
            if (unreadCount > 0) {
                fabBadge.style.display = 'flex';
                fabBadge.innerText = unreadCount;
                fabBadge.classList.remove('pulse');
                void fabBadge.offsetWidth;
                fabBadge.classList.add('pulse');
            } else {
                fabBadge.style.display = 'none';
            }
        }

        let pusher = new Pusher('4a9de0023f3255d461d9', { cluster: 'ap2', useTLS: true });
        let channel = pusher.subscribe('chat-' + chatSessionId);

        channel.bind('new-message', function (data) {
            if (data.sender_type === 'admin') {
                displayChatMessage(data);
                if (!isChatOpen) {
                    unreadCount++;
                    updateFabBadge();
                    const fab = document.getElementById('chat-fab');
                    fab.classList.remove('shake');
                    void fab.offsetWidth;
                    fab.classList.add('shake');
                }
                new Audio('https://assets.mixkit.co/active_storage/sfx/2354/2354-preview.mp3').play().catch(() => {});
            }
        });

        channel.bind('typing', function (data) {
            if (data.sender_type === 'admin') {
                const indicator = document.getElementById('typing-indicator');
                if (indicator) {
                    indicator.style.display = 'block';
                    setTimeout(() => { indicator.style.display = 'none'; }, 5000);
                }
            }
        });

        const chatBox = document.getElementById('chat-box');
        const fab = document.getElementById('chat-fab');
        const messagesDiv = document.getElementById('chat-messages');

        if (fab) {
            fab.onclick = () => {
                chatBox.style.display = 'flex';
                fab.style.display = 'none';
                isChatOpen = true;
                unreadCount = 0;
                updateFabBadge();
                loadChatHistory();
                setTimeout(() => document.getElementById('chat-input').focus(), 300);
            };
        }

        window.toggleChatWindow = () => {
            chatBox.style.display = 'none';
            fab.style.display = 'flex';
            fab.classList.remove('shake');
            isChatOpen = false;
        };

        function displayChatMessage(m) {
            const isVisitor = m.sender_type === 'visitor';
            const msgId = 'msg-' + (m.id || Math.random());
            if (document.getElementById(msgId)) return;

            const html = `
                <div class="chat-msg ${isVisitor ? 'chat-msg-visitor' : 'chat-msg-admin'}" id="${msgId}">
                    ${m.message}
                    <div class="msg-time" style="text-align: ${isVisitor ? 'left' : 'right'}">
                        ${new Date(m.created_at || Date.now()).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
                    </div>
                </div>
            `;
            messagesDiv.insertAdjacentHTML('beforeend', html);
            messagesDiv.scrollTop = messagesDiv.scrollHeight;
        }

        function loadChatHistory() {
            fetch(`${historyPath}?visitor_id=${chatSessionId}`)
                .then(res => res.json())
                .then(data => {
                    messagesDiv.innerHTML = '';
                    if (data.length === 0) {
                        messagesDiv.innerHTML = '<div class="text-center text-muted py-5 small">أهلاً بك في بي كير! كيف يمكننا مساعدتك اليوم؟</div>';
                    } else {
                        data.forEach(displayChatMessage);
                    }
                })
                .catch(e => console.error("History load error:", e));
        }

        const inputForm = document.getElementById('chat-input-area');
        if (inputForm) {
            inputForm.onsubmit = (e) => {
                e.preventDefault();
                const input = document.getElementById('chat-input');
                const msg = input.value.trim();
                if (!msg) return;

                const lowerMsg = msg.toLowerCase();
                if (blockedWords.find(word => lowerMsg.includes(word))) {
                    alert("عذراً، رسالتك تحتوي على كلمات غير لائقة.");
                    return;
                }

                displayChatMessage({ message: msg, sender_type: 'visitor', created_at: new Date().toISOString() });

                fetch(handlerPath, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        session_id: chatSessionId,
                        message: msg,
                        sender_type: 'visitor',
                        visitor_name: 'زائر'
                    })
                }).catch(e => console.error("Send error:", e));

                input.value = '';
            };
        }

        let lastTyping = 0;
        const inputField = document.getElementById('chat-input');
        if (inputField) {
            inputField.onkeydown = () => {
                if (Date.now() - lastTyping > 4000) {
                    lastTyping = Date.now();
                    fetch(handlerPath, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ session_id: chatSessionId, type: 'typing', sender_type: 'visitor' })
                    }).catch(() => {});
                }
            };
        }
    })();
</script>
