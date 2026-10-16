<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\HttpException;
use App\Helpers\FlashMessage;
use App\Helpers\Validator;
use App\Models\OrgaoUnidade;

/** Cadastro de órgãos e unidades (somente administradores). */
final class OrgaoController extends Controller
{
    public function index(): void
    {
        $this->exigirPerfil('admin');

        $this->render('admin/orgaos/index', [
            'titulo' => 'Órgãos e unidades',
            'orgaos' => (new OrgaoUnidade())->listarTodos(),
        ]);
    }

    public function create(): void
    {
        $this->exigirPerfil('admin');

        $this->renderizarFormulario('Novo órgão ou unidade', ['sigla' => '', 'nome' => '', 'situacao' => 'ativo'], [], null);
    }

    public function store(): void
    {
        $this->exigirPerfil('admin');

        $valores = $this->valoresDoFormulario('ativo');
        $validador = $this->validar($valores, null);

        if ($validador->falhou()) {
            $this->renderizarFormulario('Novo órgão ou unidade', $valores, $validador->erros(), null);

            return;
        }

        $id = (new OrgaoUnidade())->criar($valores['sigla'], $valores['nome']);

        $this->auditar('orgao.criado', 'orgaos_unidades', $id, 'Órgão cadastrado: ' . $valores['sigla']);
        FlashMessage::sucesso('Órgão cadastrado.');
        $this->redirecionar('/admin/orgaos');
    }

    public function edit(): void
    {
        $this->exigirPerfil('admin');

        $orgao = $this->localizar($this->idGet());

        $this->renderizarFormulario(
            'Editar órgão ou unidade',
            ['sigla' => $orgao['sigla'], 'nome' => $orgao['nome'], 'situacao' => $orgao['situacao']],
            [],
            $orgao
        );
    }

    public function update(): void
    {
        $this->exigirPerfil('admin');

        $orgao = $this->localizar($this->idPost());
        $id = (int) $orgao['id'];

        $valores = $this->valoresDoFormulario($this->post('situacao'));
        $validador = $this->validar($valores, $id);

        if ($validador->falhou()) {
            $this->renderizarFormulario('Editar órgão ou unidade', $valores, $validador->erros(), $orgao);

            return;
        }

        (new OrgaoUnidade())->atualizar($id, $valores['sigla'], $valores['nome'], $valores['situacao']);

        $this->auditar('orgao.atualizado', 'orgaos_unidades', $id, 'Órgão atualizado: ' . $valores['sigla']);
        FlashMessage::sucesso('Órgão atualizado.');
        $this->redirecionar('/admin/orgaos');
    }

    /** @return array<string, mixed> */
    private function localizar(int $id): array
    {
        $orgao = $id > 0 ? (new OrgaoUnidade())->buscarPorId($id) : null;

        if ($orgao === null) {
            throw new HttpException(404, 'Órgão não encontrado.');
        }

        return $orgao;
    }

    /** @return array<string, string> */
    private function valoresDoFormulario(string $situacao): array
    {
        return ['sigla' => $this->post('sigla'), 'nome' => $this->post('nome'), 'situacao' => $situacao];
    }

    private function validar(array $valores, ?int $ignorarId): Validator
    {
        $validador = (new Validator($valores))
            ->obrigatorio('sigla', 'a sigla')->tamanho('sigla', 'a sigla', 2, 20)
            ->obrigatorio('nome', 'o nome')->tamanho('nome', 'o nome', 3, 190)
            ->opcao('situacao', 'uma situação', ['ativo', 'inativo']);

        if ($valores['sigla'] !== '' && preg_match('/^[\p{L}0-9\-\/ ]+$/u', $valores['sigla']) !== 1) {
            $validador->adicionarErro('sigla', 'A sigla deve ter apenas letras, números, hífen ou barra.');
        }

        if (!isset($validador->erros()['sigla']) && (new OrgaoUnidade())->siglaExiste($valores['sigla'], $ignorarId)) {
            $validador->adicionarErro('sigla', 'Já existe um órgão com esta sigla.');
        }

        return $validador;
    }

    private function renderizarFormulario(string $titulo, array $valores, array $erros, ?array $orgao): void
    {
        if ($erros !== []) {
            http_response_code(422);
        }

        $this->render('admin/orgaos/form', ['titulo' => $titulo, 'valores' => $valores, 'erros' => $erros, 'orgao' => $orgao]);
    }
}
