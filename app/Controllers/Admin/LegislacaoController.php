<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\HttpException;
use App\Helpers\FlashMessage;
use App\Helpers\Rotulos;
use App\Helpers\Validator;
use App\Models\Legislacao;
use App\Models\Notificacao;
use App\Services\ArmazenamentoArquivos;
use DomainException;
use Throwable;

/** Cadastro de legislação e normativos (somente administradores). */
final class LegislacaoController extends Controller
{
    public function index(): void
    {
        $this->exigirPerfil('admin');

        $this->render('admin/legislacao/index', [
            'titulo' => 'Legislação e normativos',
            'documentos' => (new Legislacao())->listarParaAdmin(),
        ]);
    }

    public function create(): void
    {
        $this->exigirPerfil('admin');

        $this->renderizarFormulario('Novo documento', [
            'categoria' => 'estadual', 'numero' => '', 'titulo' => '', 'ementa' => '', 'data_publicacao' => '',
            'situacao' => 'vigente', 'url' => '', 'ativo' => '1',
        ]);
    }

    public function store(): void
    {
        $admin = $this->exigirPerfil('admin');

        $valores = $this->valoresDoFormulario();
        $temArquivo = ArmazenamentoArquivos::temEnvio($_FILES['arquivo'] ?? null);
        $validador = $this->validar($valores, $temArquivo || $valores['url'] !== '');

        if ($validador->falhou()) {
            $this->renderizarFormulario('Novo documento', $valores, $validador->erros());

            return;
        }

        $armazenamento = new ArmazenamentoArquivos();
        $arquivoId = null;

        try {
            if ($temArquivo) {
                $arquivoId = $armazenamento->salvar($_FILES['arquivo'], 'documento', (int) $admin['id']);
            }
        } catch (DomainException $e) {
            $this->renderizarFormulario('Novo documento', $valores, ['arquivo' => $e->getMessage()]);

            return;
        }

        try {
            $id = (new Legislacao())->criar($this->paraBanco($valores, $arquivoId) + ['autor_id' => (int) $admin['id']]);
        } catch (Throwable $e) {
            if ($arquivoId !== null) {
                $armazenamento->excluir($arquivoId);
            }

            throw $e;
        }

        if ($valores['ativo'] === '1') {
            (new Notificacao())->criar('legislacao', 'Nova legislação: ' . $valores['titulo'], $id);
        }

        $this->auditar('legislacao.criada', 'legislacoes', $id, 'Documento cadastrado: ' . $valores['titulo']);
        FlashMessage::sucesso('Documento cadastrado.');
        $this->redirecionar('/admin/legislacao');
    }

    public function edit(): void
    {
        $this->exigirPerfil('admin');

        $documento = $this->localizar($this->idGet());

        $this->renderizarFormulario('Editar documento', [
            'categoria' => $documento['categoria'],
            'numero' => (string) ($documento['numero'] ?? ''),
            'titulo' => $documento['titulo'],
            'ementa' => (string) ($documento['ementa'] ?? ''),
            'data_publicacao' => (string) ($documento['data_publicacao'] ?? ''),
            'situacao' => $documento['situacao'],
            'url' => (string) ($documento['url'] ?? ''),
            'ativo' => (string) $documento['ativo'],
        ], [], $documento);
    }

    public function update(): void
    {
        $admin = $this->exigirPerfil('admin');

        $documento = $this->localizar($this->idPost());
        $id = (int) $documento['id'];
        $arquivoAtual = $documento['arquivo_id'] !== null ? (int) $documento['arquivo_id'] : null;

        $valores = $this->valoresDoFormulario();
        $temArquivo = ArmazenamentoArquivos::temEnvio($_FILES['arquivo'] ?? null);
        $validador = $this->validar($valores, $temArquivo || $arquivoAtual !== null || $valores['url'] !== '');

        if ($validador->falhou()) {
            $this->renderizarFormulario('Editar documento', $valores, $validador->erros(), $documento);

            return;
        }

        $armazenamento = new ArmazenamentoArquivos();
        $arquivoFinal = $arquivoAtual;

        try {
            if ($temArquivo) {
                $arquivoFinal = $armazenamento->salvar($_FILES['arquivo'], 'documento', (int) $admin['id']);
            }
        } catch (DomainException $e) {
            $this->renderizarFormulario('Editar documento', $valores, ['arquivo' => $e->getMessage()], $documento);

            return;
        }

        try {
            (new Legislacao())->atualizar($id, $this->paraBanco($valores, $arquivoFinal));
        } catch (Throwable $e) {
            if ($arquivoFinal !== $arquivoAtual && $arquivoFinal !== null) {
                $armazenamento->excluir($arquivoFinal);
            }

            throw $e;
        }

        if ($arquivoAtual !== null && $arquivoAtual !== $arquivoFinal) {
            $armazenamento->excluir($arquivoAtual);
        }

        if ((int) $documento['ativo'] === 0 && $valores['ativo'] === '1') {
            (new Notificacao())->criar('legislacao', 'Nova legislação: ' . $valores['titulo'], $id);
        }

        $this->auditar('legislacao.atualizada', 'legislacoes', $id, 'Documento atualizado: ' . $valores['titulo']);
        FlashMessage::sucesso('Documento atualizado.');
        $this->redirecionar('/admin/legislacao');
    }

    /** @return array<string, mixed> */
    private function localizar(int $id): array
    {
        $documento = $id > 0 ? (new Legislacao())->buscarPorId($id) : null;

        if ($documento === null) {
            throw new HttpException(404, 'Documento não encontrado.');
        }

        return $documento;
    }

    /** @return array<string, string> */
    private function valoresDoFormulario(): array
    {
        return [
            'categoria' => $this->post('categoria'),
            'numero' => $this->post('numero'),
            'titulo' => $this->post('titulo'),
            'ementa' => $this->post('ementa'),
            'data_publicacao' => $this->post('data_publicacao'),
            'situacao' => $this->post('situacao'),
            'url' => $this->post('url'),
            'ativo' => $this->post('ativo') === '1' ? '1' : '0',
        ];
    }

    /** @param bool $temOrigem Indica se haverá arquivo ou link para consulta do documento. */
    private function validar(array $valores, bool $temOrigem): Validator
    {
        $validador = (new Validator($valores))
            ->opcao('categoria', 'uma categoria', array_keys(Rotulos::CATEGORIAS_LEGISLACAO))
            ->tamanho('numero', 'o número', 1, 60)
            ->obrigatorio('titulo', 'o título')->tamanho('titulo', 'o título', 5, 190)
            ->tamanho('ementa', 'o resumo', 10, 1000)
            ->data('data_publicacao', 'a data de publicação')
            ->opcao('situacao', 'uma situação', array_keys(Rotulos::SITUACOES_LEGISLACAO))
            ->tamanho('url', 'o endereço', 10, 500)->url('url', 'o endereço do documento');

        if (!$temOrigem) {
            $validador->adicionarErro('arquivo', 'Envie o arquivo do documento ou informe o endereço (link) para consulta.');
        }

        return $validador;
    }

    /** @return array<string, mixed> */
    private function paraBanco(array $valores, ?int $arquivoId): array
    {
        $nulo = static fn (string $v): ?string => $v === '' ? null : $v;

        return [
            'categoria' => $valores['categoria'],
            'numero' => $nulo($valores['numero']),
            'titulo' => $valores['titulo'],
            'ementa' => $nulo($valores['ementa']),
            'data_publicacao' => $nulo($valores['data_publicacao']),
            'situacao' => $valores['situacao'],
            'url' => $nulo($valores['url']),
            'arquivo_id' => $arquivoId,
            'ativo' => (int) $valores['ativo'],
        ];
    }

    private function renderizarFormulario(string $titulo, array $valores, array $erros = [], ?array $documento = null): void
    {
        if ($erros !== []) {
            http_response_code(422);
        }

        $this->render('admin/legislacao/form', [
            'titulo' => $titulo,
            'valores' => $valores,
            'erros' => $erros,
            'documento' => $documento,
            'limiteMb' => ArmazenamentoArquivos::limiteMb('documento'),
        ]);
    }
}
