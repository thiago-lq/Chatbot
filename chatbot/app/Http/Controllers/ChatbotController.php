<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Http;

class ChatbotController extends Controller
{
    private $apiKey;
    private $apiUrl = 'https://router.huggingface.co/v1/chat/completions';
    private $model = 'meta-llama/Llama-3.1-8B-Instruct';

    public function __construct() {
        $this->apiKey = env('HUGGING_API_KEY');
    }

    public function index() {
        // chamar o 
        return view('chatbot.index');
    }

    public function sendMessage(Request $request) {
        // Enviar mensagem e receber a resposta
        $request->validate([
            'message' => 'required|string|max:500'
        ]);

        $userMessage = $request->input('message');
        $conversationHistory = Session::get('conversation_history', []);
        $systemPrompt = $this->getSystemPrompt();
        $messages = $this->prepareMessage($systemPrompt, $conversationHistory, $userMessage);

        $response = HTTP::timeout(30)
        ->withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json'
        ])

        ->post($this->apiUrl, [
            'messages' => $messages,
            'model' => $this->model,
            'max_tokens' => 1000,
            'temperature' => 0.2,
            'stream' => false
        ]);

        if($response->successful()) {
            $assistentMessage = $response->json()['choices'][0]['message']['content'];

            $conversationHistory[] = [
                'role' => 'user',
                'content' => $userMessage,
            ];

            $conversationHistory[] = [
                'role' => 'assistent',
                'content' => $assistentMessage,
            ];

            if(count($conversationHistory) > 20) {
                $conversationHistory = array_slice($conversationHistory, -20);
            }
            Session::put('conversation_history', $conversationHistory);

            return response()->json([
                'success' => true,
                'message' => $assistentMessage
            ]);
        }  {
            return response()->json([
                'success' => false,
                'message' => "Erro ao comunicar com a API: " . $response->body()
            ]);
        }
    }

    public function getHistory() {
        // Pegar historico de mensagens
        return response()->json([
            'success' => true,
            'history' => Session::get('conversation_history', [])
        ]);
    }

    public function cleanHistory() {
        // limpa historico de mensagens
        Session::forget('conversation_history');
        return response()->json([
            'success' => true,
            'history' => "Apagado com sucesso"
        ]);
    }

    public function getSystemPrompt() {
        // Retorna a descrição completa da nossa empresa
        $descricao = "Você é o Mestre de RPG, o narrador da história. Sua missão é conduzir a aventura, 
                    descrevendo cenários de forma imersiva e vívida, dando vida aos personagens que não pertencem ao jogador e 
                    controlando inimigos, aliados e acontecimentos do mundo. Nunca decida as ações do personagem do jogador: 
                    apenas apresente o ambiente, as consequências e os desdobramentos das escolhas que ele fizer. 
                    Sempre traga descrições ricas que estimulem a imaginação, criando atmosferas tensas, misteriosas, épicas ou sombrias, 
                    conforme o tom da cena.
                    Quando o jogador agir, você deve narrar o resultado dessa ação e mostrar como o mundo reage a ela, 
                    abrindo novos caminhos ou desafios. Use diálogos para dar personalidade aos NPCs, faça o mundo parecer vivo e mantenha a sensação de que cada decisão importa. 
                    Sempre que possível, ofereça escolhas ou pistas para que o jogador tenha liberdade de decidir seu rumo. 
                    Caso precise haver sorte, combate ou habilidade, descreva o resultado narrativo sem entrar em números ou regras, a não ser que o jogador peça.
                    Seu tom deve ser o de um contador de histórias, mantendo a imersão em primeiro lugar. 
                    O jogador será o protagonista de uma aventura em constante movimento, e você será os olhos, ouvidos e vozes de tudo ao redor dele."
            ;
        return $descricao;
    }

    public function prepareMessage($systemPrompt, $conversationHistory, $userMessage) {
        // Preparar a mensagem para a api
        $messages =[
            [
                'role' => 'system',
                'content' => $systemPrompt
            ],
        ];

        foreach ($conversationHistory as $message) {
            $messages[] = $message;
        }

        $messages[] = [
            'role' => 'user',
            'content' => $userMessage
        ];

        return $messages;
    }

}
