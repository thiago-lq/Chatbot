<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Chatbot - TechEdu Academy</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .chat-container {
            width: 90%;
            max-width: 800px;
            height: 90vh;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .chat-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            text-align: center;
            position: relative;
        }

        .chat-header h1 {
            font-size: 24px;
            margin-bottom: 5px;
        }

        .chat-header p {
            font-size: 14px;
            opacity: 0.9;
        }

        .clear-btn {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
            transition: background 0.3s;
        }

        .clear-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            background: #f5f5f5;
        }

        .message {
            margin-bottom: 15px;
            display: flex;
            animation: fadeIn 0.3s;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .message.user {
            justify-content: flex-end;
        }

        .message-content {
            max-width: 70%;
            padding: 12px 16px;
            border-radius: 18px;
            word-wrap: break-word;
        }

        .message.user .message-content {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-bottom-right-radius: 4px;
        }

        .message.assistant .message-content {
            background: white;
            color: #333;
            border-bottom-left-radius: 4px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        /* Estilos para conteúdo markdown */
        .message-content p {
            margin-bottom: 10px;
            line-height: 1.6;
        }

        .message-content p:last-child {
            margin-bottom: 0;
        }

        .message-content ul,
        .message-content ol {
            margin: 10px 0;
            padding-left: 25px;
        }

        .message-content ul li,
        .message-content ol li {
            margin-bottom: 5px;
            line-height: 1.6;
        }

        .message-content ul {
            list-style-type: disc;
        }

        .message-content ol {
            list-style-type: decimal;
        }

        .message-content ul ul,
        .message-content ol ul {
            list-style-type: circle;
            margin-top: 5px;
        }

        .message-content ul ul ul,
        .message-content ol ul ul {
            list-style-type: square;
        }

        .message-content h1,
        .message-content h2,
        .message-content h3,
        .message-content h4,
        .message-content h5,
        .message-content h6 {
            margin-top: 15px;
            margin-bottom: 10px;
            font-weight: bold;
            line-height: 1.3;
        }

        .message-content h1:first-child,
        .message-content h2:first-child,
        .message-content h3:first-child,
        .message-content h4:first-child,
        .message-content h5:first-child,
        .message-content h6:first-child {
            margin-top: 0;
        }

        .message-content h1 { font-size: 1.8em; }
        .message-content h2 { font-size: 1.5em; }
        .message-content h3 { font-size: 1.3em; }
        .message-content h4 { font-size: 1.1em; }
        .message-content h5 { font-size: 1em; }
        .message-content h6 { font-size: 0.9em; }

        .message-content code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', Courier, monospace;
            font-size: 0.9em;
        }

        .message.user .message-content code {
            background: rgba(255, 255, 255, 0.2);
        }

        .message-content pre {
            background: #f4f4f4;
            padding: 12px;
            border-radius: 5px;
            overflow-x: auto;
            margin: 10px 0;
        }

        .message-content pre code {
            background: transparent;
            padding: 0;
        }

        .message-content a {
            color: #667eea;
            text-decoration: none;
        }

        .message-content a:hover {
            text-decoration: underline;
        }

        .message.user .message-content a {
            color: #fff;
            text-decoration: underline;
        }

        .message-content strong {
            font-weight: bold;
        }

        .message-content em {
            font-style: italic;
        }

        .message-content blockquote {
            border-left: 4px solid #667eea;
            padding-left: 15px;
            margin: 10px 0;
            color: #666;
            font-style: italic;
        }

        .message.user .message-content blockquote {
            border-left-color: rgba(255, 255, 255, 0.5);
            color: rgba(255, 255, 255, 0.9);
        }

        .message-content table {
            border-collapse: collapse;
            width: 100%;
            margin: 10px 0;
        }

        .message-content table th,
        .message-content table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        .message-content table th {
            background-color: #f4f4f4;
            font-weight: bold;
        }

        .message-content hr {
            border: none;
            border-top: 1px solid #ddd;
            margin: 15px 0;
        }

        .chat-input-container {
            padding: 20px;
            background: white;
            border-top: 1px solid #e0e0e0;
        }

        .chat-input-form {
            display: flex;
            gap: 10px;
        }

        .chat-input {
            flex: 1;
            padding: 12px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 25px;
            font-size: 14px;
            outline: none;
            transition: border-color 0.3s;
        }

        .chat-input:focus {
            border-color: #667eea;
        }

        .send-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            transition: transform 0.2s;
        }

        .send-btn:hover {
            transform: scale(1.05);
        }

        .send-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: scale(1);
        }

        .loading {
            display: none;
            text-align: center;
            padding: 10px;
            color: #666;
        }

        .loading.active {
            display: block;
        }

        .loading-dots {
            display: inline-block;
        }

        .loading-dots span {
            animation: blink 1.4s infinite;
            font-size: 20px;
        }

        .loading-dots span:nth-child(2) {
            animation-delay: 0.2s;
        }

        .loading-dots span:nth-child(3) {
            animation-delay: 0.4s;
        }

        @keyframes blink {
            0%, 80%, 100% {
                opacity: 0;
            }
            40% {
                opacity: 1;
            }
        }

        .welcome-message {
            text-align: center;
            padding: 40px 20px;
            color: #666;
        }

        .welcome-message h2 {
            color: #667eea;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="chat-container">
        <div class="chat-header">
            <h1>🤖 TechEdu Academy</h1>
            <p>Assistente Virtual - Como posso te ajudar?</p>
            <button class="clear-btn" onclick="clearChat()">Limpar</button>
        </div>

        <div class="chat-messages" id="chatMessages">
            <div class="welcome-message">
                <h2>Bem-vindo! 👋</h2>
                <p>Olá! Sou o assistente virtual da TechEdu Academy.<br>
                Pergunte-me sobre nossos cursos, valores, horários ou qualquer dúvida!</p>
            </div>
        </div>

        <div class="loading" id="loading">
            <div class="loading-dots">
                <span>.</span><span>.</span><span>.</span>
            </div>
            Digitando...
        </div>

        <div class="chat-input-container">
            <form class="chat-input-form" id="chatForm" onsubmit="sendMessage(event)">
                <input 
                    type="text" 
                    class="chat-input" 
                    id="messageInput" 
                    placeholder="Digite sua mensagem..."
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

        // Carrega o histórico ao iniciar
        window.onload = function() {
            loadHistory();
        };

        function addMessage(content, role) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${role}`;
            
            const contentDiv = document.createElement('div');
            contentDiv.className = 'message-content';
            if (role !== 'user') content = marked.parse(content);
            contentDiv.innerHTML = content;
            
            messageDiv.appendChild(contentDiv);
            chatMessages.appendChild(messageDiv);
            
            // Remove a mensagem de boas-vindas se existir
            const welcomeMsg = chatMessages.querySelector('.welcome-message');
            if (welcomeMsg) {
                welcomeMsg.remove();
            }
            
            // Scroll automático para a última mensagem
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        async function sendMessage(event) {
            event.preventDefault();
            
            const message = messageInput.value.trim();
            if (!message) return;
            
            // Adiciona mensagem do usuário
            addMessage(message, 'user');
            messageInput.value = '';
            
            // Desabilita o botão e mostra loading
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
                
                if (data.success) {
                    addMessage(data.message, 'assistant');
                } else {
                    addMessage('Desculpe, ocorreu um erro. Tente novamente.', 'assistant');
                }
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
            if (!confirm('Deseja limpar todo o histórico da conversa?')) {
                return;
            }
            
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
                            <h2>Bem-vindo! 👋</h2>
                            <p>Olá! Sou o assistente virtual da TechEdu Academy.<br>
                            Pergunte-me sobre nossos cursos, valores, horários ou qualquer dúvida!</p>
                        </div>
                    `;
                }
            } catch (error) {
                console.error('Erro ao limpar:', error);
                alert('Erro ao limpar o histórico.');
            }
        }

        async function loadHistory() {
            try {
                const response = await fetch('/chatbot/history');
                const data = await response.json();
                
                if (data.success && data.history.length > 0) {
                    // Limpa mensagem de boas-vindas
                    const welcomeMsg = chatMessages.querySelector('.welcome-message');
                    if (welcomeMsg) {
                        welcomeMsg.remove();
                    }
                    
                    // Adiciona histórico
                    data.history.forEach(msg => {
                        addMessage(msg.content, msg.role);
                    });
                }
            } catch (error) {
                console.error('Erro ao carregar histórico:', error);
            }
        }
    </script>
</body>
</html>