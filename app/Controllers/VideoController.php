<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\HttpException;
use App\Helpers\Rotulos;
use App\Models\Arquivo;
use App\Models\Video;
use App\Services\EnvioArquivo;

/** Vídeos operacionais: consulta e reprodução. */
final class VideoController extends Controller
{
    public function index(): void
    {
        $this->exigirLogin();

        $busca = mb_substr($this->get('q'), 0, 100);
        $categoria = array_key_exists($this->get('categoria'), Rotulos::CATEGORIAS) ? $this->get('categoria') : '';
        $ordem = in_array($this->get('ordem'), ['recentes', 'antigos', 'titulo'], true) ? $this->get('ordem') : 'recentes';

        $this->render('videos/index', [
            'titulo' => 'Vídeos operacionais',
            'videos' => (new Video())->listarPublicos($busca, $categoria, $ordem),
            'filtros' => ['q' => $busca, 'categoria' => $categoria, 'ordem' => $ordem],
        ]);
    }

    public function ver(): void
    {
        $this->exigirLogin();

        $video = $this->localizarAtivo($this->idGet());

        $this->auditar('video.visualizado', 'videos', (int) $video['id'], 'Vídeo visualizado: ' . $video['titulo']);

        $this->render('videos/ver', ['titulo' => $video['titulo'], 'video' => $video]);
    }

    /** Entrega o arquivo de vídeo enviado (com suporte a Range para o player). */
    public function arquivo(): void
    {
        $this->exigirLogin();

        $video = $this->localizarAtivo($this->idGet());
        $arquivo = $video['origem'] === 'arquivo' && $video['arquivo_id'] !== null
            ? (new Arquivo())->buscarPorId((int) $video['arquivo_id'])
            : null;

        if ($arquivo === null) {
            throw new HttpException(404, 'Vídeo sem arquivo.');
        }

        EnvioArquivo::enviar($arquivo, true);
    }

    /** @return array<string, mixed> */
    private function localizarAtivo(int $id): array
    {
        $video = $id > 0 ? (new Video())->buscarPorId($id) : null;

        if ($video === null || (int) $video['ativo'] !== 1) {
            throw new HttpException(404, 'Vídeo não encontrado.');
        }

        return $video;
    }
}
