<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>لوحة تحكم الدردشة (المدير)</title>
    <style>
        body { font-family: Tahoma, Arial, sans-serif; background: #f0f2f5; margin: 0; padding: 20px; }
        .chat-container { max-width: 800px; margin: auto; background: #fff; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); display: flex; flex-direction: column; overflow: hidden; height: 80vh; }
        .header { background: #156394; color: #fff; padding: 15px; font-weight: bold; text-align: center; }
        .messages { flex: 1; padding: 20px; overflow-y: auto; display: flex; flex-direction: column; gap: 10px; background: #e9ecef; }
        .message-row { display: flex; flex-direction: column; max-width: 70%; }
        .message-row.admin { align-self: flex-start; }
        .message-row.visitor { align-self: flex-end; }
        .msg-bubble { padding: 10px 15px; border-radius: 15px; word-wrap: break-word; }
        .admin .msg-bubble { background: #156394; color: white; border-top-right-radius: 0; }
        .visitor .msg-bubble { background: #fff; color: #000; border-top-left-radius: 0; border: 1px solid #ccc; }
        .input-area { padding: 15px; background: #fff; display: flex; gap: 10px; border-top: 1px solid #ddd; }
        input[type="text"] { flex: 1; padding: 10px; border: 1px solid #ccc; border-radius: 4px; }
        select { padding: 10px; border: 1px solid #ccc; border-radius: 4px; }
        button { padding: 10px 20px; background: #156394; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; }
        button:hover { background: #12557a; }
        .visitor-id { font-size: 11px; color: gray; margin-bottom: 3px; }
    </style>
</head>
<body>

<div class="chat-container">
    <div class="header">لوحة الإدارة - الرد على الزوار</div>
    
    <div class="messages" id="messages">
        <!-- Messages will go here -->
    </div>
    
    <div id="typing" style="display:none; padding: 0 20px; font-size: 12px; color: gray;">أحد الزوار يكتب الآن...</div>
    
    <div class="input-area">
        <select id="target-session">
            <option value="admin">اختر زائر للرد عليه</option>
            <!-- Sessions will populate here -->
        </select>
        <input type="text" id="chat-input" placeholder="اكتب ردك هنا...">
        <button id="send-btn">إرسال</button>
    </div>
</div>

<script>
    let conn;
    let activeSessions = new Set();
    const adminSessionId = 'admin';
    let typingTimeout = null;

    function connect() {
        conn = new WebSocket('ws://localhost:8080');

        conn.onopen = function(e) {
            console.log("Admin Connected!");
            conn.send(JSON.stringify({ type: 'init', session_id: adminSessionId, name: 'المدير' }));
            
            const urlParams = new URLSearchParams(window.location.search);
            const preSelectSession = urlParams.get('session_id');
            loadHistory(preSelectSession);
        };

        conn.onmessage = function(e) {
            const data = JSON.parse(e.data);
            if(data.type === 'message') {
                document.getElementById('typing').style.display = 'none';
                
                if (data.sender_type === 'visitor') {
                    if (!activeSessions.has(data.session_id)) {
                        activeSessions.add(data.session_id);
                        const sel = document.getElementById('target-session');
                        const opt = document.createElement('option');
                        opt.value = data.session_id;
                        opt.text = "زائر: " + data.session_id.substring(0, 15) + "...";
                        sel.appendChild(opt);
                        if(sel.options.length === 2) sel.selectedIndex = 1;
                    }
                }
                displayMessage(data);
            } 
            else if (data.type === 'typing' && data.sender_type === 'visitor') {
                document.getElementById('typing').style.display = 'block';
                clearTimeout(typingTimeout);
                typingTimeout = setTimeout(() => { document.getElementById('typing').style.display = 'none'; }, 2000);
            }
        };

        conn.onclose = function(e) {
            setTimeout(connect, 3000);
        };
    }

    function loadHistory(preSelectSession = null) {
        fetch(`../chat_history.php?is_admin=1${preSelectSession ? '&visitor_id=' + preSelectSession : ''}`)
            .then(res => res.json())
            .then(messages => {
                const messagesDiv = document.getElementById('messages');
                messagesDiv.innerHTML = '';
                messages.forEach(m => {
                    if (m.sender_type === 'visitor' && !activeSessions.has(m.session_id)) {
                        activeSessions.add(m.session_id);
                        const sel = document.getElementById('target-session');
                        const opt = document.createElement('option');
                        opt.value = m.session_id;
                        opt.text = "زائر: " + m.session_id.substring(0, 15) + "...";
                        sel.appendChild(opt);
                    }
                    displayMessage(m);
                });
                
                if (preSelectSession) {
                    const sel = document.getElementById('target-session');
                    sel.value = preSelectSession;
                }
            });
    }

    function displayMessage(data) {
        const messagesDiv = document.getElementById('messages');
        const isAdmin = data.sender_type === 'admin';
        const rowClass = isAdmin ? 'admin' : 'visitor';
        
        // Only show message if it's an admin message or if it belongs to a visitor session we want to track
        // In basic admin panel, we show ALL messages in one stream for now
        messagesDiv.innerHTML += `
            <div class="message-row ${rowClass}">
                <div class="visitor-id">${isAdmin ? 'أنت' : ('زائر ' + data.session_id.substring(0,8))}</div>
                <div class="msg-bubble">${data.message}</div>
                <div style="font-size:10px; color:gray; text-align: ${isAdmin ? 'right' : 'left'}; margin-top:2px;">${data.time || ''}</div>
            </div>`;
        messagesDiv.scrollTop = messagesDiv.scrollHeight;
    }

    function sendMessage() {
        const input = document.getElementById('chat-input');
        const targetSession = document.getElementById('target-session').value;
        const msg = input.value.trim();

        if (msg !== '' && conn.readyState === WebSocket.OPEN) {
            if (targetSession === 'admin') {
                alert('الرجاء اختيار زائر للرد عليه');
                return;
            }
            conn.send(JSON.stringify({
                type: 'message',
                session_id: targetSession,
                sender_type: 'admin',
                message: msg
            }));
            input.value = '';
        }
    }

    connect();

    document.getElementById('send-btn').addEventListener('click', sendMessage);
    document.getElementById('chat-input').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') sendMessage();
    });
</script>

</body>
</html>
