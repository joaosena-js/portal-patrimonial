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

/** Módulos de conteúdo: gestão pelos administradores e leitura pelos gestores. */
final class ModuloController extends Controller
{
    // ---------- Área administrativa ----------

    public function index(): void
    {
        $this->exigirPerfil('admin');

        $trilha = $this->localizarTrilha($this->idGet('trilha_id'));

        $this->render('modulos/index', [
            'titulo' => 'Módulos da trilha',
            'trilha' => $trilha,
            'modulos' => (new Modulo())->listarPorTrilha((int) $trilha['id'], false),
        ]);
    }

    public function create(): void
    {
        $this->exigirPerfil('admin');

        $trilha = $this->localizarTrilha($this->idGet('trilha_id'));

        $this->renderizarFormulario('modulos/cadastrar', 'Novo módulo', $trilha, [
            'titulo' => '',
            'descricao' => '',
            'tipo' => 'texto',
            'conteudo_url' => '',
            'conteudo_texto' => '',
            'ordem' => (string) (new Modulo())->proximaOrdem((int) $trilha['id']),
            'carga_horaria_min' => '',
            'ativo' => '1',
        ]);
    }

    public function store(): void
    {
        $this->exigirPerfil('admin');

        $trilha = $this->localizarTrilha($this->idPost('trilha_id'));
        $trilhaId = (int) $trilha['id'];
        $modelo = new Modulo();

        $valores = $this->valoresDoFormulario();

        if ($valores['ordem'] === '') {
            $valores['ordem'] = (string) $modelo->proximaOrdem($trilhaId);
        }

        $validador = $this->validar($valores);

        if ($validador->falhou()) {
            $this->renderizarFormulario('modulos/cadastrar', 'Novo módulo', $trilha, $valores, $validador->erros());

            return;
        }

        $id = $modelo->criar(['trilha_id' => $trilhaId] + $this->paraBanco($valores));

        $this->auditar('modulo.criado', 'modulos', $id, 'Módulo criado: ' . $valores['titulo']);
        FlashMessage::sucesso('Módulo cadastrado.');
        $this->redirecionar('/admin/modulos', ['trilha_id' => $trilhaId]);
    }

    public function edit(): void
    {
        $this->exigirPerfil('admin');

        $modulo = $this->localizarModulo($this->idGet());
        $trilha = $this->localizarTrilha((int) $modulo['trilha_id']);

        $this->renderizarFormulario('modulos/editar', 'Editar módulo', $trilha, [
            'titulo' => $modulo['titulo'],
            'descricao' => (string) ($modulo['descricao'] ?? ''),
            'tipo' => $modulo['tipo'],
            'conteudo_url' => (string) ($modulo['conteudo_url'] ?? ''),
            'conteudo_texto' => (string) ($modulo['conteudo_texto'] ?? ''),
            'ordem' => (string) $modulo['ordem'],
            'carga_horaria_min' => (string) ($modulo['carga_horaria_min'] ?? ''),
            'ativo' => (string) $modulo['ativo'],
        ], [], $modulo);
    }

    public function update(): void
    {
        $this->exigirPerfil('admin');

        $modulo = $this->localizarModulo($this->idPost());
        $trilha = $this->localizarTrilha((int) $modulo['trilha_id']);

        $valores = $this->valoresDoFormulario();

        if ($valores['ordem'] === '') {
            $valores['ordem'] = (string) $modulo['ordem'];
        }

        $validador = $this->validar($valores);

        if ($validador->falhou()) {
            $this->renderizarFormulario('modulos/editar', 'Editar módulo', $trilha, $valores, $validador->erros(), $modulo);

            return;
        }

        (new Modulo())->atualizar((int) $modulo['id'], $this->paraBanco($valores));

        $this->auditar('modulo.atualizado', 'modulos', (int) $modulo['id'], 'Módulo atualizado: ' . $valores['titulo']);
        FlashMessage::sucesso('Módulo atualizado.');
        $this->redirecionar('/admin/modulos', ['trilha_id' => (int) $trilha['id']]);
    }

    // ---------- Área do gestor ----------

    public function show(): void
    {
        $usuario = $this->exigirPerfil('gestor');
        $usuarioId = (int) $usuario['id'];

        $modulo = $this->localizarModulo($this->idGet());

        if ((int) $modulo['ativo'] !== 1) {
            throw new HttpException(404, 'Módulo inativo.');
        }

        $trilha = $this->localizarTrilha((int) $modulo['trilha_id']);

        if ($trilha['situacao'] !== 'publicada') {
            throw new HttpException(404, 'Trilha não publicada.');
        }

        $trilhaId = (int) $trilha['id'];

        if ((new Inscricao())->buscar($usuarioId, $trilhaId) === null) {
            FlashMessage::aviso('Inicie a trilha para acessar os módulos.');
            $this->redirecionar('/gestor/trilha', ['id' => $trilhaId]);
        }

        $modulos = (new Modulo())->listarPorTrilha($trilhaId, true);
        $posicao = 0;

        foreach ($modulos as $indice => $item) {
            if ((int) $item['id'] === (int) $modulo['id']) {
                $posicao = $indice;
                break;
            }
        }

        $concluidos = (new Progresso())->idsConcluidos($usuarioId, $trilhaId);

        $this->render('modulos/visualizar', [
            'titulo' => $modulo['titulo'],
            'trilha' => $trilha,
            'modulo' => $modulo,
            'anterior' => $modulos[$posicao - 1] ?? null,
            'proximo' => $modulos[$posicao + 1] ?? null,
            'posicao' => $posicao + 1,
            'totalModulos' => count($modulos),
            'concluido' => in_array((int) $modulo['id'], $concluidos, true),
        ]);
    }

    // ---------- Apoio ----------

    /** @return array<string, mixed> */
    private function localizarTrilha(int $id): array
    {
        $trilha = $id > 0 ? (new Trilha())->buscarPorId($id) : null;

        if ($trilha === null) {
            throw new HttpException(404, 'Trilha não encontrada.');
        }

        return $trilha;
    }

    /** @return array<string, mixed> */
    private function localizarModulo(int $id): array
    {
        $modulo = $id > 0 ? (new Modulo())->buscarPorId($id) : null;

        if ($modulo === null) {
            throw new HttpException(404, 'Módulo não encontrado.');
        }

        return $modulo;
    }

    /** @return array<string, string> */
    private function valoresDoFormulario(): array
    {
        return [
            'titulo' => $this->post('titulo'),
            'descricao' => $this->post('descricao'),
            'tipo' => $this->post('tipo'),
            'conteudo_url' => $this->post('conteudo_url'),
            'conteudo_texto' => $this->post('conteudo_texto'),
            'ordem' => $this->post('ordem'),
            'carga_horaria_min' => $this->post('carga_horaria_min'),
            'ativo' => $this->post('ativo') === '1' ? '1' : '0',
        ];
    }

    private function validar(array $valores): Validator
    {
        $validador = (new Validator($valores))
            ->obrigatorio('titulo', 'o título')->tamanho('titulo', 'o título', 3, 150)
            ->tamanho('descricao', 'a descrição', 5, 500)
            ->opcao('tipo', 'um tipo', array_keys(Rotulos::TIPOS_MODULO))
            ->inteiro('ordem', 'a ordem', 1, 9999)
            ->inteiro('carga_horaria_min', 'a carga horária', 1, 600);

        if ($valores['tipo'] === 'texto') {
            $validador->obrigatorio('conteudo_texto', 'o conteúdo do módulo')->tamanho('conteudo_texto', 'o conteúdo', 10, 20000);
        } else {
            $validador->obrigatorio('conteudo_url', 'o endereço do conteúdo')
                ->tamanho('conteudo_url', 'o endereço', 10, 500)
                ->url('conteudo_url', 'o endereço do conteúdo');
        }

        return $validador;
    }

    /**
     * Converte os valores do formulário para o formato do banco.
     * Guarda apenas o campo de conteúdo que corresponde ao tipo escolhido.
     *
     * @return array<string, mixed>
     */
    private function paraBanco(array $valores): array
    {
        $ehTexto = $valores['tipo'] === 'texto';

        return [
            'titulo' => $valores['titulo'],
            'descricao' => $valores['descricao'] === '' ? null : $valores['descricao'],
            'tipo' => $valores['tipo'],
            'conteudo_url' => $ehTexto ? null : $valores['conteudo_url'],
            'conteudo_texto' => $ehTexto ? $valores['conteudo_texto'] : null,
            'ordem' => (int) $valores['ordem'],
            'carga_horaria_min' => $valores['carga_horaria_min'] === '' ? null : (int) $valores['carga_horaria_min'],
            'ativo' => (int) $valores['ativo'],
        ];
    }

    private function renderizarFormulario(
        string $view,
        string $titulo,
        array $trilha,
        array $valores,
        array $erros = [],
        ?array $modulo = null
    ): void {
        if ($erros !== []) {
            http_response_code(422);
        }

        $this->render($view, [
            'titulo' => $titulo,
            'trilha' => $trilha,
            'valores' => $valores,
            'erros' => $erros,
            'modulo' => $modulo,
        ]);
    }
}
