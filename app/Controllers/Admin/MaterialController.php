<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\HttpException;
use App\Helpers\FlashMessage;
use App\Helpers\Rotulos;
use App\Helpers\Validator;
use App\Models\Material;
use App\Models\MaterialGrupo;
use App\Models\Notificacao;
use App\Services\ArmazenamentoArquivos;
use DomainException;
use Throwable;

/** Materiais de apoio e seus grupos (assuntos do accordion). Somente administradores. */
final class MaterialController extends Controller
{
    // ---------- Materiais ----------

    public function index(): void
    {
        $this->exigirPerfil('admin');

        $this->render('admin/materiais/index', [
            'titulo' => 'Materiais de apoio',
            'materiais' => (new Material())->listarParaAdmin(),
        ]);
    }

    public function create(): void
    {
        $this->exigirPerfil('admin');

        $this->renderizarFormulario('Novo material', [
            'grupo_id' => $this->get('grupo_id'), 'titulo' => '', 'descricao' => '', 'tipo' => 'checklist',
            'publicado_em' => date('Y-m-d'), 'ativo' => '1',
        ]);
    }

    public function store(): void
    {
        $admin = $this->exigirPerfil('admin');

        $valores = $this->valoresDoFormulario();
        $validador = $this->validar($valores, true);

        if ($validador->falhou()) {
            $this->renderizarFormulario('Novo material', $valores, $validador->erros());

            return;
        }

        $armazenamento = new ArmazenamentoArquivos();

        try {
            $arquivoId = $armazenamento->salvar($_FILES['arquivo'] ?? null, 'documento', (int) $admin['id']);
        } catch (DomainException $e) {
            $this->renderizarFormulario('Novo material', $valores, ['arquivo' => $e->getMessage()]);

            return;
        }

        try {
            $id = (new Material())->criar($this->paraBanco($valores, $arquivoId) + ['autor_id' => (int) $admin['id']]);
        } catch (Throwable $e) {
            $armazenamento->excluir($arquivoId);

            throw $e;
        }

        if ($valores['ativo'] === '1') {
            (new Notificacao())->criar('material', $this->tituloNotificacao($valores), $id);
        }

        $this->auditar('material.criado', 'materiais', $id, 'Material publicado: ' . $valores['titulo']);
        FlashMessage::sucesso('Material cadastrado.');
        $this->redirecionar('/admin/materiais');
    }

    public function edit(): void
    {
        $this->exigirPerfil('admin');

        $material = $this->localizar($this->idGet());

        $this->renderizarFormulario('Editar material', [
            'grupo_id' => (string) $material['grupo_id'],
            'titulo' => $material['titulo'],
            'descricao' => (string) ($material['descricao'] ?? ''),
            'tipo' => $material['tipo'],
            'publicado_em' => $material['publicado_em'],
            'ativo' => (string) $material['ativo'],
        ], [], $material);
    }

    public function update(): void
    {
        $admin = $this->exigirPerfil('admin');

        $material = $this->localizar($this->idPost());
        $id = (int) $material['id'];
        $arquivoAtual = (int) $material['arquivo_id'];

        $valores = $this->valoresDoFormulario();
        $validador = $this->validar($valores, false);

        if ($validador->falhou()) {
            $this->renderizarFormulario('Editar material', $valores, $validador->erros(), $material);

            return;
        }

        $armazenamento = new ArmazenamentoArquivos();
        $arquivoFinal = $arquivoAtual;

        try {
            if (ArmazenamentoArquivos::temEnvio($_FILES['arquivo'] ?? null)) {
                $arquivoFinal = $armazenamento->salvar($_FILES['arquivo'], 'documento', (int) $admin['id']);
            }
        } catch (DomainException $e) {
            $this->renderizarFormulario('Editar material', $valores, ['arquivo' => $e->getMessage()], $material);

            return;
        }

        try {
            (new Material())->atualizar($id, $this->paraBanco($valores, $arquivoFinal));
        } catch (Throwable $e) {
            if ($arquivoFinal !== $arquivoAtual) {
                $armazenamento->excluir($arquivoFinal);
            }

            throw $e;
        }

        if ($arquivoFinal !== $arquivoAtual) {
            $armazenamento->excluir($arquivoAtual);
        }

        if ((int) $material['ativo'] === 0 && $valores['ativo'] === '1') {
            (new Notificacao())->criar('material', $this->tituloNotificacao($valores), $id);
        }

        $this->auditar('material.atualizado', 'materiais', $id, 'Material atualizado: ' . $valores['titulo']);
        FlashMessage::sucesso('Material atualizado.');
        $this->redirecionar('/admin/materiais');
    }

    // ---------- Grupos (assuntos) ----------

    public function gruposIndex(): void
    {
        $this->exigirPerfil('admin');

        $this->render('admin/materiais/grupos', [
            'titulo' => 'Assuntos dos materiais',
            'grupos' => (new MaterialGrupo())->listarTodos(),
        ]);
    }

    public function grupoCreate(): void
    {
        $this->exigirPerfil('admin');

        $this->renderizarFormularioGrupo('Novo assunto', [
            'titulo' => '', 'descricao' => '', 'ordem' => (string) (new MaterialGrupo())->proximaOrdem(), 'ativo' => '1',
        ]);
    }

    public function grupoStore(): void
    {
        $this->exigirPerfil('admin');

        $valores = $this->valoresDoGrupo();
        $validador = $this->validarGrupo($valores);

        if ($validador->falhou()) {
            $this->renderizarFormularioGrupo('Novo assunto', $valores, $validador->erros());

            return;
        }

        $id = (new MaterialGrupo())->criar(
            $valores['titulo'],
            $valores['descricao'] === '' ? null : $valores['descricao'],
            (int) $valores['ordem']
        );

        $this->auditar('material_grupo.criado', 'material_grupos', $id, 'Assunto criado: ' . $valores['titulo']);
        FlashMessage::sucesso('Assunto cadastrado.');
        $this->redirecionar('/admin/materiais/grupos');
    }

    public function grupoEdit(): void
    {
        $this->exigirPerfil('admin');

        $grupo = $this->localizarGrupo($this->idGet());

        $this->renderizarFormularioGrupo('Editar assunto', [
            'titulo' => $grupo['titulo'],
            'descricao' => (string) ($grupo['descricao'] ?? ''),
            'ordem' => (string) $grupo['ordem'],
            'ativo' => (string) $grupo['ativo'],
        ], [], $grupo);
    }

    public function grupoUpdate(): void
    {
        $this->exigirPerfil('admin');

        $grupo = $this->localizarGrupo($this->idPost());
        $id = (int) $grupo['id'];

        $valores = $this->valoresDoGrupo();
        $validador = $this->validarGrupo($valores);

        if ($validador->falhou()) {
            $this->renderizarFormularioGrupo('Editar assunto', $valores, $validador->erros(), $grupo);

            return;
        }

        (new MaterialGrupo())->atualizar(
            $id,
            $valores['titulo'],
            $valores['descricao'] === '' ? null : $valores['descricao'],
            (int) $valores['ordem'],
            (int) $valores['ativo']
        );

        $this->auditar('material_grupo.atualizado', 'material_grupos', $id, 'Assunto atualizado: ' . $valores['titulo']);
        FlashMessage::sucesso('Assunto atualizado.');
        $this->redirecionar('/admin/materiais/grupos');
    }

    // ---------- Apoio ----------

    /** @return array<string, mixed> */
    private function localizar(int $id): array
    {
        $material = $id > 0 ? (new Material())->buscarPorId($id) : null;

        if ($material === null) {
            throw new HttpException(404, 'Material não encontrado.');
        }

        return $material;
    }

    /** @return array<string, mixed> */
    private function localizarGrupo(int $id): array
    {
        $grupo = $id > 0 ? (new MaterialGrupo())->buscarPorId($id) : null;

        if ($grupo === null) {
            throw new HttpException(404, 'Assunto não encontrado.');
        }

        return $grupo;
    }

    /** @return array<string, string> */
    private function valoresDoFormulario(): array
    {
        return [
            'grupo_id' => $this->post('grupo_id'),
            'titulo' => $this->post('titulo'),
            'descricao' => $this->post('descricao'),
            'tipo' => $this->post('tipo'),
            'publicado_em' => $this->post('publicado_em'),
            'ativo' => $this->post('ativo') === '1' ? '1' : '0',
        ];
    }

    private function validar(array $valores, bool $arquivoObrigatorio): Validator
    {
        $validador = (new Validator($valores))
            ->obrigatorio('grupo_id', 'o assunto')
            ->obrigatorio('titulo', 'o título')->tamanho('titulo', 'o título', 3, 150)
            ->tamanho('descricao', 'a descrição', 5, 500)
            ->opcao('tipo', 'um tipo', array_keys(Rotulos::TIPOS_MATERIAL))
            ->obrigatorio('publicado_em', 'a data de publicação')->data('publicado_em', 'a data de publicação');

        $grupoId = filter_var($valores['grupo_id'], FILTER_VALIDATE_INT);

        if ($valores['grupo_id'] !== '' && ($grupoId === false || (new MaterialGrupo())->buscarPorId($grupoId) === null)) {
            $validador->adicionarErro('grupo_id', 'Selecione um assunto válido.');
        }

        if ($arquivoObrigatorio && !ArmazenamentoArquivos::temEnvio($_FILES['arquivo'] ?? null)) {
            $validador->adicionarErro('arquivo', 'Selecione o arquivo do material (PDF, DOCX ou XLSX).');
        }

        return $validador;
    }

    /** @return array<string, mixed> */
    private function paraBanco(array $valores, int $arquivoId): array
    {
        return [
            'grupo_id' => (int) $valores['grupo_id'],
            'titulo' => $valores['titulo'],
            'descricao' => $valores['descricao'] === '' ? null : $valores['descricao'],
            'tipo' => $valores['tipo'],
            'arquivo_id' => $arquivoId,
            'publicado_em' => $valores['publicado_em'],
            'ativo' => (int) $valores['ativo'],
        ];
    }

    private function tituloNotificacao(array $valores): string
    {
        return ($valores['tipo'] === 'modelo' ? 'Novo modelo: ' : 'Novo material: ') . $valores['titulo'];
    }

    /** @return array<string, string> */
    private function valoresDoGrupo(): array
    {
        return [
            'titulo' => $this->post('titulo'),
            'descricao' => $this->post('descricao'),
            'ordem' => $this->post('ordem'),
            'ativo' => $this->post('ativo') === '1' ? '1' : '0',
        ];
    }

    private function validarGrupo(array $valores): Validator
    {
        return (new Validator($valores))
            ->obrigatorio('titulo', 'o título do assunto')->tamanho('titulo', 'o título', 3, 150)
            ->tamanho('descricao', 'a descrição', 5, 500)
            ->obrigatorio('ordem', 'a ordem')->inteiro('ordem', 'a ordem', 1, 9999);
    }

    private function renderizarFormulario(string $titulo, array $valores, array $erros = [], ?array $material = null): void
    {
        if ($erros !== []) {
            http_response_code(422);
        }

        $this->render('admin/materiais/form', [
            'titulo' => $titulo,
            'valores' => $valores,
            'erros' => $erros,
            'material' => $material,
            'grupos' => (new MaterialGrupo())->listarTodos(),
            'limiteMb' => ArmazenamentoArquivos::limiteMb('documento'),
        ]);
    }

    private function renderizarFormularioGrupo(string $titulo, array $valores, array $erros = [], ?array $grupo = null): void
    {
        if ($erros !== []) {
            http_response_code(422);
        }

        $this->render('admin/materiais/grupo_form', ['titulo' => $titulo, 'valores' => $valores, 'erros' => $erros, 'grupo' => $grupo]);
    }
}
