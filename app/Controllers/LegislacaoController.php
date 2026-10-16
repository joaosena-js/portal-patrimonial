<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\HttpException;
use App\Helpers\Rotulos;
use App\Models\Arquivo;
use App\Models\Legislacao;
use App\Services\EnvioArquivo;

/** Legislação e normativos: pesquisa e download. */
final class LegislacaoController extends Controller
{
    public function index(): void
    {
        $this->exigirLogin();

        $busca = mb_substr($this->get('q'), 0, 100);
        $categoria = array_key_exists($this->get('categoria'), Rotulos::CATEGORIAS_LEGISLACAO) ? $this->get('categoria') : '';

        $this->render('legislacao/index', [
            'titulo' => 'Legislação e normativos',
            'documentos' => (new Legislacao())->listarPublicos($busca, $categoria),
            'filtros' => ['q' => $busca, 'categoria' => $categoria],
        ]);
    }

    public function baixar(): void
    {
        $this->exigirLogin();

        $id = $this->idGet();
        $documento = $id > 0 ? (new Legislacao())->buscarPorId($id) : null;

        if ($documento === null || (int) $documento['ativo'] !== 1 || $documento['arquivo_id'] === null) {
            throw new HttpException(404, 'Documento não encontrado.');
        }

        $arquivo = (new Arquivo())->buscarPorId((int) $documento['arquivo_id']);

        if ($arquivo === null) {
            throw new HttpException(404, 'Arquivo do documento não encontrado.');
        }

        $this->auditar('legislacao.download', 'legislacoes', $id, 'Download: ' . $documento['titulo']);

        EnvioArquivo::enviar($arquivo, false);
    }
}
