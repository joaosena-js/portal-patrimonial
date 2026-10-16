<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\HttpException;
use App\Helpers\FlashMessage;
use App\Helpers\Rotulos;
use App\Helpers\Texto;
use App\Helpers\Validator;
use App\Models\Notificacao;
use App\Models\Video;
use App\Services\ArmazenamentoArquivos;
use DomainException;
use Throwable;

/** Publicação e manutenção de vídeos operacionais (somente administradores). */
final class VideoController extends Controller
{
    public function index(): void
    {
        $this->exigirPerfil('admin');

        $this->render('admin/videos/index', [
            'titulo' => 'Vídeos operacionais',
            'videos' => (new Video())->listarParaAdmin(),
        ]);
    }

    public function create(): void
    {
        $this->exigirPerfil('admin');

        $this->renderizarFormulario('Novo vídeo', [
            'titulo' => '', 'descricao' => '', 'categoria' => 'cadastro', 'area_responsavel' => 'cpat',
            'origem' => 'youtube', 'url' => '', 'publicado_em' => date('Y-m-d'), 'ativo' => '1',
        ]);
    }

    public function store(): void
    {
        $admin = $this->exigirPerfil('admin');

        $valores = $this->valoresDoFormulario();
        $validador = $this->validar($valores, $valores['origem'] === 'arquivo');

        if ($validador->falhou()) {
            $this->renderizarFormulario('Novo vídeo', $valores, $validador->erros());

            return;
        }

        $armazenamento = new ArmazenamentoArquivos();
        $arquivoId = null;

        try {
            if ($valores['origem'] === 'arquivo') {
                $arquivoId = $armazenamento->salvar($_FILES['arquivo'] ?? null, 'video', (int) $admin['id']);
            }
        } catch (DomainException $e) {
            $this->renderizarFormulario('Novo vídeo', $valores, ['arquivo' => $e->getMessage()]);

            return;
        }

        try {
            $id = (new Video())->criar($this->paraBanco($valores, $arquivoId) + ['autor_id' => (int) $admin['id']]);
        } catch (Throwable $e) {
            if ($arquivoId !== null) {
                $armazenamento->excluir($arquivoId);
            }

            throw $e;
        }

        if ($valores['ativo'] === '1') {
            (new Notificacao())->criar('video', 'Novo vídeo: ' . $valores['titulo'], $id);
        }

        $this->auditar('video.criado', 'videos', $id, 'Vídeo publicado: ' . $valores['titulo']);
        FlashMessage::sucesso('Vídeo cadastrado.');
        $this->redirecionar('/admin/videos');
    }

    public function edit(): void
    {
        $this->exigirPerfil('admin');

        $video = $this->localizar($this->idGet());

        $this->renderizarFormulario('Editar vídeo', [
            'titulo' => $video['titulo'],
            'descricao' => (string) ($video['descricao'] ?? ''),
            'categoria' => $video['categoria'],
            'area_responsavel' => $video['area_responsavel'],
            'origem' => $video['origem'],
            'url' => (string) ($video['url'] ?? ''),
            'publicado_em' => $video['publicado_em'],
            'ativo' => (string) $video['ativo'],
        ], [], $video);
    }

    public function update(): void
    {
        $admin = $this->exigirPerfil('admin');

        $video = $this->localizar($this->idPost());
        $id = (int) $video['id'];
        $arquivoAtual = $video['arquivo_id'] !== null ? (int) $video['arquivo_id'] : null;

        $valores = $this->valoresDoFormulario();
        // O arquivo só é obrigatório se o vídeo ainda não tiver um arquivo guardado.
        $validador = $this->validar($valores, $valores['origem'] === 'arquivo' && $arquivoAtual === null);

        if ($validador->falhou()) {
            $this->renderizarFormulario('Editar vídeo', $valores, $validador->erros(), $video);

            return;
        }

        $armazenamento = new ArmazenamentoArquivos();
        $arquivoFinal = $valores['origem'] === 'arquivo' ? $arquivoAtual : null;
        $novoArquivo = null;

        try {
            if ($valores['origem'] === 'arquivo' && ArmazenamentoArquivos::temEnvio($_FILES['arquivo'] ?? null)) {
                $novoArquivo = $armazenamento->salvar($_FILES['arquivo'], 'video', (int) $admin['id']);
                $arquivoFinal = $novoArquivo;
            }
        } catch (DomainException $e) {
            $this->renderizarFormulario('Editar vídeo', $valores, ['arquivo' => $e->getMessage()], $video);

            return;
        }

        try {
            (new Video())->atualizar($id, $this->paraBanco($valores, $arquivoFinal));
        } catch (Throwable $e) {
            if ($novoArquivo !== null) {
                $armazenamento->excluir($novoArquivo);
            }

            throw $e;
        }

        // Remove o arquivo antigo se foi substituído ou se o vídeo passou a ser um link.
        if ($arquivoAtual !== null && $arquivoAtual !== $arquivoFinal) {
            $armazenamento->excluir($arquivoAtual);
        }

        // Um vídeo que estava oculto e voltou a ser exibido conta como novidade.
        if ((int) $video['ativo'] === 0 && $valores['ativo'] === '1') {
            (new Notificacao())->criar('video', 'Novo vídeo: ' . $valores['titulo'], $id);
        }

        $this->auditar('video.atualizado', 'videos', $id, 'Vídeo atualizado: ' . $valores['titulo']);
        FlashMessage::sucesso('Vídeo atualizado.');
        $this->redirecionar('/admin/videos');
    }

    /** @return array<string, mixed> */
    private function localizar(int $id): array
    {
        $video = $id > 0 ? (new Video())->buscarPorId($id) : null;

        if ($video === null) {
            throw new HttpException(404, 'Vídeo não encontrado.');
        }

        return $video;
    }

    /** @return array<string, string> */
    private function valoresDoFormulario(): array
    {
        return [
            'titulo' => $this->post('titulo'),
            'descricao' => $this->post('descricao'),
            'categoria' => $this->post('categoria'),
            'area_responsavel' => $this->post('area_responsavel'),
            'origem' => $this->post('origem'),
            'url' => $this->post('url'),
            'publicado_em' => $this->post('publicado_em'),
            'ativo' => $this->post('ativo') === '1' ? '1' : '0',
        ];
    }

    private function validar(array $valores, bool $arquivoObrigatorio): Validator
    {
        $validador = (new Validator($valores))
            ->obrigatorio('titulo', 'o título')->tamanho('titulo', 'o título', 3, 150)
            ->tamanho('descricao', 'a descrição', 5, 1000)
            ->opcao('categoria', 'uma categoria', array_keys(Rotulos::CATEGORIAS))
            ->opcao('area_responsavel', 'uma área responsável', array_keys(Rotulos::AREAS_RESPONSAVEIS))
            ->opcao('origem', 'uma origem', array_keys(Rotulos::ORIGENS_VIDEO))
            ->obrigatorio('publicado_em', 'a data de publicação')->data('publicado_em', 'a data de publicação');

        if (in_array($valores['origem'], ['youtube', 'link'], true)) {
            $validador->obrigatorio('url', 'o endereço do vídeo')
                ->tamanho('url', 'o endereço', 10, 500)
                ->url('url', 'o endereço do vídeo');

            if ($valores['origem'] === 'youtube' && !isset($validador->erros()['url']) && Texto::youtubeId($valores['url']) === null) {
                $validador->adicionarErro('url', 'Não foi possível identificar um vídeo do YouTube neste endereço.');
            }
        }

        if ($arquivoObrigatorio && !ArmazenamentoArquivos::temEnvio($_FILES['arquivo'] ?? null)) {
            $validador->adicionarErro('arquivo', 'Selecione o arquivo de vídeo (MP4 ou WebM).');
        }

        return $validador;
    }

    /** @return array<string, mixed> */
    private function paraBanco(array $valores, ?int $arquivoId): array
    {
        $origem = $valores['origem'];

        return [
            'titulo' => $valores['titulo'],
            'descricao' => $valores['descricao'] === '' ? null : $valores['descricao'],
            'categoria' => $valores['categoria'],
            'area_responsavel' => $valores['area_responsavel'],
            'origem' => $origem,
            'url' => $origem === 'arquivo' ? null : $valores['url'],
            'youtube_id' => $origem === 'youtube' ? Texto::youtubeId($valores['url']) : null,
            'arquivo_id' => $origem === 'arquivo' ? $arquivoId : null,
            'publicado_em' => $valores['publicado_em'],
            'ativo' => (int) $valores['ativo'],
        ];
    }

    private function renderizarFormulario(string $titulo, array $valores, array $erros = [], ?array $video = null): void
    {
        if ($erros !== []) {
            http_response_code(422);
        }

        $this->render('admin/videos/form', [
            'titulo' => $titulo,
            'valores' => $valores,
            'erros' => $erros,
            'video' => $video,
            'limiteMb' => ArmazenamentoArquivos::limiteMb('video'),
        ]);
    }
}
