<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Mestre de RPG IA</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #3a1c71 0%, #d76d77 100%);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .chat-container {
            width: 90%;
            max-width: 800px;
            height: 90vh;
            background: #f9f5f2;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        .chat-header {
            background: linear-gradient(135deg, #3a1c71 0%, #d76d77 100%);
            color: white;
            padding: 20px;
            text-align: center;
            position: relative;
        }
        .chat-header h1 { font-size: 24px; margin-bottom: 5px; }
        .chat-header p { font-size: 14px; opacity: 0.9; }
        .clear-btn {
            position: absolute; right: 20px; top: 50%; transform: translateY(-50%);
            background: rgba(255,255,255,0.2); border: none; color: white;
            padding: 8px 15px; border-radius: 5px; cursor: pointer; font-size: 12px;
            transition: background 0.3s;
        }
        .clear-btn:hover { background: rgba(255,255,255,0.3); }
        .chat-messages { flex: 1; overflow-y: auto; padding: 20px; background: #efe6db; }
        .message { margin-bottom: 15px; display: flex; animation: fadeIn 0.3s; }
        @keyframes fadeIn { from {opacity:0; transform:translateY(10px);} to {opacity:1; transform:translateY(0);} }
        .message.user { justify-content: flex-end; }
        .message-content {
            max-width: 70%; padding: 12px 16px; border-radius: 18px; word-wrap: break-word;
        }
        .message.user .message-content {
            background: linear-gradient(135deg, #3a1c71 0%, #d76d77 100%); color: white; border-bottom-right-radius: 4px;
        }
        .message.assistant .message-content {
            background: #fff8f0; color: #333; border-bottom-left-radius: 4px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .message-content p { margin-bottom: 10px; line-height: 1.6; }
        .message-content p:last-child { margin-bottom: 0; }
        .message-content ul, .message-content ol { margin: 10px 0; padding-left: 25px; }
        .message-content ul li, .message-content ol li { margin-bottom: 5px; line-height: 1.6; }
        .message-content ul { list-style-type: disc; }
        .message-content ol { list-style-type: decimal; }
        .chat-input-container { padding: 20px; background: #f9f5f2; border-top: 1px solid #ddd; }
        .chat-input-form { display: flex; gap: 10px; }
        .chat-input {
            flex: 1; padding: 12px 16px; border: 2px solid #ddd; border-radius: 25px;
            font-size: 14px; outline: none; transition: border-color 0.3s;
        }
        .chat-input:focus { border-color: #3a1c71; }
        .send-btn {
            background: linear-gradient(135deg, #3a1c71 0%, #d76d77 100%);
            color: white; border: none; padding: 12px 30px; border-radius: 25px;
            cursor: pointer; font-size: 14px; font-weight: bold; transition: transform 0.2s;
        }
        .send-btn:hover { transform: scale(1.05); }
        .send-btn:disabled { opacity: 0.6; cursor: not-allowed; transform: scale(1); }
        .loading { display: none; text-align: center; padding: 10px; color: #666; }
        .loading.active { display: block; }
        .loading-dots { display: inline-block; }
        .loading-dots span { animation: blink 1.4s infinite; font-size: 20px; }
        .loading-dots span:nth-child(2) { animation-delay: 0.2s; }
        .loading-dots span:nth-child(3) { animation-delay: 0.4s; }
        @keyframes blink { 0%,80%,100% {opacity:0;} 40% {opacity:1;} }
        .welcome-message { text-align:center; padding:40px 20px; color:#666; }
        .welcome-message h2 { color:#3a1c71; margin-bottom:10px; }
    </style>
</head>
<body>
    <div class="chat-container">
        <div class="chat-header">
            <h1>🎲 Mestre de RPG IA</h1>
            <p>Seu mestre virtual de aventuras. O que você faz?</p>
            <button class="clear-btn" onclick="clearChat()">Limpar</button>
        </div>

        <div class="chat-messages" id="chatMessages">
            <div class="welcome-message">
                <h2>Bem-vindo, aventureiro! 👋</h2>
                <p>O Mestre está pronto para narrar sua jornada.<br>
                Digite sua ação e prepare-se para o desconhecido!</p>
            </div>
        </div>

        <div class="loading" id="loading">
            <div class="loading-dots">
                <span>.</span><span>.</span><span>.</span>
            </div>
            O Mestre está pensando...
        </div>

        <div class="chat-input-container">
            <form class="chat-input-form" id="chatForm" onsubmit="sendMessage(event)">
                <input 
                    type="text" 
                    class="chat-input" 
                    id="messageInput" 
                    placeholder="Digite sua ação..."
                    autocomplete="off"
                    required
                >
                <button type="submit" class="send-btn" id="sendBtn">Enviar</button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <script>
        const chatMessages = document.getElementById('chatMessages');
        const messageInput = document.getElementById('messageInput');
        const chatForm = document.getElementById('chatForm');
        const sendBtn = document.getElementById('sendBtn');
        const loading = document.getElementById('loading');

        window.onload = loadHistory;

        function addMessage(content, role) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${role}`;

            const contentDiv = document.createElement('div');
            contentDiv.className = 'message-content';
            if (role !== 'user') content = marked.parse(content);
            contentDiv.innerHTML = content;

            messageDiv.appendChild(contentDiv);
            chatMessages.appendChild(messageDiv);

            const welcomeMsg = chatMessages.querySelector('.welcome-message');
            if (welcomeMsg) welcomeMsg.remove();

            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        async function sendMessage(event) {
            event.preventDefault();
            const message = messageInput.value.trim();
            if (!message) return;

            addMessage(message, 'user');
            messageInput.value = '';

            sendBtn.disabled = true;
            loading.classList.add('active');

            try {
                const response = await fetch('/chatbot/send', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ message: message })
                });

                const data = await response.json();
                if (data.success) addMessage(data.message, 'assistant');
                else addMessage('O Mestre ficou confuso. Tente novamente.', 'assistant');
            } catch (error) {
                console.error('Erro:', error);
                addMessage('Erro ao enviar mensagem. Verifique sua conexão.', 'assistant');
            } finally {
                sendBtn.disabled = false;
                loading.classList.remove('active');
                messageInput.focus();
            }
        }

        async function clearChat() {
            if (!confirm('Deseja limpar todo o histórico da aventura?')) return;

            try {
                const response = await fetch('/chatbot/clear', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await response.json();
                if (data.success) {
                    chatMessages.innerHTML = `
                        <div class="welcome-message">
                            <h2>Bem-vindo, aventureiro! 👋</h2>
                            <p>O Mestre está pronto para narrar sua jornada.<br>
                            Digite sua ação e prepare-se para o desconhecido!</p>
                        </div>
                    `;
                }
            } catch (error) {
                console.error('Erro ao limpar:', error);
                alert('Erro ao limpar o histórico da aventura.');
            }
        }

        async function loadHistory() {
            try {
                const response = await fetch('/chatbot/history');
                const data = await response.json();
                if (data.success && data.history.length > 0) {
                    const welcomeMsg = chatMessages.querySelector('.welcome-message');
                    if (welcomeMsg) welcomeMsg.remove();
                    data.history.forEach(msg => addMessage(msg.content, msg.role));
                }
            } catch (error) {
                console.error('Erro ao carregar histórico:', error);
            }
        }
    </script>
</body>
</html>
