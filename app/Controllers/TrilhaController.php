<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\HttpException;
use App\Helpers\FlashMessage;
use App\Helpers\Rotulos;
use App\Helpers\Validator;
use App\Models\Inscricao;
use App\Models\Modulo;
use App\Models\Progresso;
use App\Models\Trilha;

/** Trilhas: gestão pelos administradores e consulta pelos gestores. */
final class TrilhaController extends Controller
{
    // ---------- Área administrativa ----------

    public function index(): void
    {
        $this->exigirPerfil('admin');

        $this->render('trilhas/index', [
            'titulo' => 'Trilhas de capacitação',
            'trilhas' => (new Trilha())->listarParaAdmin(),
        ]);
    }

    public function create(): void
    {
        $this->exigirPerfil('admin');

        $this->renderizarFormulario('trilhas/cadastrar', 'Nova trilha', [
            'titulo' => '',
            'descricao' => '',
            'area' => 'geral',
            'situacao' => 'rascunho',
        ]);
    }

    public function store(): void
    {
        $admin = $this->exigirPerfil('admin');

        $valores = $this->valoresDoFormulario();
        $valores['situacao'] = 'rascunho';
        $validador = $this->validar($valores);

        if ($validador->falhou()) {
            $this->renderizarFormulario('trilhas/cadastrar', 'Nova trilha', $valores, $validador->erros());

            return;
        }

        $id = (new Trilha())->criar([
            'titulo' => $valores['titulo'],
            'descricao' => $valores['descricao'] === '' ? null : $valores['descricao'],
            'area' => $valores['area'],
            'criado_por' => (int) $admin['id'],
        ]);

        $this->auditar('trilha.criada', 'trilhas', $id, 'Trilha criada: ' . $valores['titulo']);
        FlashMessage::sucesso('Trilha criada como rascunho. Cadastre os módulos e depois publique.');
        $this->redirecionar('/admin/modulos', ['trilha_id' => $id]);
    }

    public function edit(): void
    {
        $this->exigirPerfil('admin');

        $trilha = $this->localizar($this->idGet());

        $this->renderizarFormulario('trilhas/editar', 'Editar trilha', [
            'titulo' => $trilha['titulo'],
            'descricao' => (string) ($trilha['descricao'] ?? ''),
            'area' => $trilha['area'],
            'situacao' => $trilha['situacao'],
        ], [], $trilha);
    }

    public function update(): void
    {
        $this->exigirPerfil('admin');

        $trilha = $this->localizar($this->idPost());
        $id = (int) $trilha['id'];

        $valores = $this->valoresDoFormulario();
        $valores['situacao'] = $this->post('situacao');
        $validador = $this->validar($valores);

        // Regra de negócio: uma trilha só pode ser publicada se tiver ao menos um módulo ativo.
        if (
            !$validador->falhou()
            && $valores['situacao'] === 'publicada'
            && (new Modulo())->contarAtivos($id) === 0
        ) {
            $validador->adicionarErro('situacao', 'Cadastre ao menos um módulo ativo antes de publicar a trilha.');
        }

        if ($validador->falhou()) {
            $this->renderizarFormulario('trilhas/editar', 'Editar trilha', $valores, $validador->erros(), $trilha);

            return;
        }

        (new Trilha())->atualizar($id, [
            'titulo' => $valores['titulo'],
            'descricao' => $valores['descricao'] === '' ? null : $valores['descricao'],
            'area' => $valores['area'],
            'situacao' => $valores['situacao'],
        ]);

        $this->auditar('trilha.atualizada', 'trilhas', $id, 'Trilha atualizada: ' . $valores['titulo']);
        FlashMessage::sucesso('Trilha atualizada.');
        $this->redirecionar('/admin/trilhas');
    }

    // ---------- Área do gestor ----------

    public function catalogo(): void
    {
        $usuario = $this->exigirPerfil('gestor');

        $this->render('trilhas/catalogo', [
            'titulo' => 'Trilhas de capacitação',
            'trilhas' => (new Trilha())->listarPublicadasParaGestor((int) $usuario['id']),
        ]);
    }

    public function show(): void
    {
        $usuario = $this->exigirPerfil('gestor');
        $usuarioId = (int) $usuario['id'];

        $trilha = $this->localizar($this->idGet());

        if ($trilha['situacao'] !== 'publicada') {
            throw new HttpException(404, 'Trilha não publicada.');
        }

        $trilhaId = (int) $trilha['id'];
        $inscricao = (new Inscricao())->buscar($usuarioId, $trilhaId);

        $this->render('trilhas/visualizar', [
            'titulo' => $trilha['titulo'],
            'trilha' => $trilha,
            'modulos' => (new Modulo())->listarPorTrilha($trilhaId, true),
            'inscricao' => $inscricao,
            'concluidos' => $inscricao !== null ? (new Progresso())->idsConcluidos($usuarioId, $trilhaId) : [],
        ]);
    }

    public function inscrever(): void
    {
        $usuario = $this->exigirPerfil('gestor');

        $trilha = $this->localizar($this->idPost('trilha_id'));

        if ($trilha['situacao'] !== 'publicada') {
            throw new HttpException(404, 'Trilha não publicada.');
        }

        $trilhaId = (int) $trilha['id'];

        if ((new Inscricao())->inscrever((int) $usuario['id'], $trilhaId)) {
            $this->auditar('trilha.inscricao', 'trilhas', $trilhaId, 'Início da trilha: ' . $trilha['titulo']);
            FlashMessage::sucesso('Trilha iniciada. Bons estudos.');
        }

        $this->redirecionar('/gestor/trilha', ['id' => $trilhaId]);
    }

    // ---------- Apoio ----------

    /** @return array<string, mixed> */
    private function localizar(int $id): array
    {
        $trilha = $id > 0 ? (new Trilha())->buscarPorId($id) : null;

        if ($trilha === null) {
            throw new HttpException(404, 'Trilha não encontrada.');
        }

        return $trilha;
    }

    /** @return array<string, string> */
    private function valoresDoFormulario(): array
    {
        return [
            'titulo' => $this->post('titulo'),
            'descricao' => $this->post('descricao'),
            'area' => $this->post('area'),
        ];
    }

    private function validar(array $valores): Validator
    {
        return (new Validator($valores))
            ->obrigatorio('titulo', 'o título')->tamanho('titulo', 'o título', 5, 150)
            ->tamanho('descricao', 'a descrição', 10, 1000)
            ->opcao('area', 'uma área', array_keys(Rotulos::AREAS))
            ->opcao('situacao', 'uma situação', array_keys(Rotulos::SITUACOES_TRILHA));
    }

    private function renderizarFormulario(
        string $view,
        string $titulo,
        array $valores,
        array $erros = [],
        ?array $trilha = null
    ): void {
        if ($erros !== []) {
            http_response_code(422);
        }

        $this->render($view, [
            'titulo' => $titulo,
            'valores' => $valores,
            'erros' => $erros,
            'trilha' => $trilha,
        ]);
    }
}
