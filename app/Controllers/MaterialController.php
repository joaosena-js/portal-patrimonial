<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\HttpException;
use App\Helpers\Rotulos;
use App\Models\Arquivo;
use App\Models\Material;
use App\Services\EnvioArquivo;

/** Materiais de apoio: consulta em accordion por assunto e download. */
final class MaterialController extends Controller
{
    public function index(): void
    {
        $this->exigirLogin();

        $busca = mb_substr($this->get('q'), 0, 100);
        $tipo = array_key_exists($this->get('tipo'), Rotulos::TIPOS_MATERIAL) ? $this->get('tipo') : '';

        // Agrupa os materiais por assunto, mantendo a ordem definida pelo administrador.
        $grupos = [];

        foreach ((new Material())->listarPublicos($busca, $tipo) as $material) {
            $grupoId = (int) $material['grupo_id'];
            $grupos[$grupoId]['titulo'] = $material['grupo_titulo'];
            $grupos[$grupoId]['itens'][] = $material;
        }

        $this->render('materiais/index', [
            'titulo' => 'Materiais de apoio',
            'grupos' => $grupos,
            'filtros' => ['q' => $busca, 'tipo' => $tipo],
            // Grupo aberto: o indicado na URL ou todos quando há filtro ativo.
            'grupoAberto' => $this->idGet('grupo'),
            'abrirTodos' => $busca !== '' || $tipo !== '',
        ]);
    }

    public function baixar(): void
    {
        $this->exigirLogin();

        $id = $this->idGet();
        $material = $id > 0 ? (new Material())->buscarPorId($id) : null;

        if ($material === null || (int) $material['ativo'] !== 1) {
            throw new HttpException(404, 'Material não encontrado.');
        }

        $arquivo = (new Arquivo())->buscarPorId((int) $material['arquivo_id']);

        if ($arquivo === null) {
            throw new HttpException(404, 'Arquivo do material não encontrado.');
        }

        $this->auditar('material.download', 'materiais', $id, 'Download: ' . $material['titulo']);

        EnvioArquivo::enviar($arquivo, false);
    }
}
