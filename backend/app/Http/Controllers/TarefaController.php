<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\JsonResponse;

class TarefaController extends Controller
{
    private string $jsonPath;

    public function __construct()
    {
        $this->jsonPath = storage_path('app/tarefas.json');
    }

    private function lerTarefas(): array
    {
        if (!file_exists($this->jsonPath)) {
            $tarefasIniciais = [
                ['id' => 1, 'title' => 'Tarefa 1', 'completed' => false],
                ['id' => 2, 'title' => 'Tarefa 2', 'completed' => true],
                ['id' => 3, 'title' => 'Tarefa 3', 'completed' => false],
            ];
            $this->salvarTarefas($tarefasIniciais);
            return $tarefasIniciais;
        }

        $conteudo = @file_get_contents($this->jsonPath);

        if ($conteudo === false) {
            Log::error("Erro ao ler o arquivo JSON.");
            return [];
        }

        $dados = json_decode($conteudo, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error("Erro ao decodificar JSON: " . json_last_error_msg());
            return [];
        }

        return $dados;
    }

    private function salvarTarefas(array $tarefas): void
    {
        file_put_contents($this->jsonPath, json_encode($tarefas, JSON_PRETTY_PRINT));
    }

    public function index(): JsonResponse
    {
        $tarefas = $this->lerTarefas();
        return response()->json($tarefas);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $tarefas = $this->lerTarefas();

        $novaTarefa = [
            'id' => count($tarefas) > 0 ? max(array_column($tarefas, 'id')) + 1 : 1,
            'title' => $validated['title'],
            'completed' => false,
        ];

        $tarefas[] = $novaTarefa;
        $this->salvarTarefas($tarefas);

        return response()->json($novaTarefa, 201);
    }

    public function destroy(int $id): JsonResponse
    {
        $tarefas = $this->lerTarefas();
        $index = array_search($id, array_column($tarefas, 'id'));

        if ($index === false) {
            return response()->json(['message' => 'Tarefa não encontrada'], 404);
        }

        array_splice($tarefas, $index, 1);
        $this->salvarTarefas($tarefas);

        return response()->json(null, 204);
    }
}