<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Helpers\Rotulos;
use App\Models\Faq;

/** Perguntas frequentes: consulta por todos os usuários autenticados. */
final class FaqController extends Controller
{
    public function index(): void
    {
        $this->exigirLogin();

        $busca = mb_substr($this->get('q'), 0, 100);
        $categoria = array_key_exists($this->get('categoria'), Rotulos::CATEGORIAS) ? $this->get('categoria') : '';

        $modelo = new Faq();
        $perguntas = $modelo->listarPublicas($busca, $categoria);
        $vinculos = $modelo->vinculosDetalhados(array_map(static fn (array $p): int => (int) $p['id'], $perguntas));

        // Agrupa por categoria respeitando a ordem de Rotulos::CATEGORIAS.
        $porCategoria = [];

        foreach (array_keys(Rotulos::CATEGORIAS) as $chave) {
            foreach ($perguntas as $pergunta) {
                if ($pergunta['categoria'] === $chave) {
                    $porCategoria[$chave][] = $pergunta;
                }
            }
        }

        $this->render('faq/index', [
            'titulo' => 'Perguntas frequentes',
            'porCategoria' => $porCategoria,
            'vinculos' => $vinculos,
            'total' => count($perguntas),
            'filtros' => ['q' => $busca, 'categoria' => $categoria],
        ]);
    }
}
