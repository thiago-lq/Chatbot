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
            'max_tokens' => 200,
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

            if(count($conversationHistory) > 10) {
                $conversationHistory = array_slice($conversationHistory, -10);
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
        $descricaoEmpresa = "O Pet Shop Mundo Animal é um espaço completo voltado para a saúde, o cuidado e a alegria dos animais de estimação. 
                Nosso compromisso é oferecer não apenas produtos de qualidade, mas também um atendimento atencioso e especializado, garantindo confiança e bem-estar para pets e tutores.
                Produtos:Rações premium e especiais para diferentes portes, idades e necessidades nutricionais.
                Petiscos naturais e funcionais para o enriquecimento alimentar.
                Brinquedos educativos e recreativos, que estimulam o instinto e a atividade física dos animais.
                Acessórios diversos, como camas, casinhas, coleiras, guias, roupas e itens de transporte.
                Produtos de higiene e beleza, incluindo shampoos, perfumes e itens antipulgas e carrapatos.
                Serviços:
                Banho e Tosa: realizadas por profissionais treinados, utilizando produtos dermatologicamente testados, adaptados a cada tipo de pelagem.
                Atendimento veterinário básico: orientações de saúde, vacinação e primeiros cuidados.
                Day Care e Hotelzinho: espaços seguros e monitorados para pets que precisam de companhia durante o dia ou estadias mais longas.
                Orientação nutricional: suporte para tutores que buscam a alimentação mais adequada para seus animais.
                Diferenciais:
                Ambiente climatizado, confortável e higienizado.
                Equipe apaixonada por animais, sempre atualizada com cursos e treinamentos.
                Espaço pensado para que os pets se sintam acolhidos e tranquilos.
                Atendimento personalizado, reconhecendo as particularidades de cada bichinho.
                Nosso objetivo é ir além da ideia de um simples pet shop: queremos ser um ponto de confiança e referência para tutores que buscam cuidado, carinho e qualidade de vida para seus animais de estimação.
                No Mundo Animal, cada visita é uma experiência única, porque acreditamos que todo pet merece amor, atenção e respeito."
            ;
        return $descricaoEmpresa;
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
