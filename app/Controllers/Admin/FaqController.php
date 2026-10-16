<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\HttpException;
use App\Helpers\FlashMessage;
use App\Helpers\Rotulos;
use App\Helpers\Validator;
use App\Models\Contato;
use App\Models\Faq;
use App\Models\Legislacao;
use App\Models\Material;
use App\Models\Notificacao;
use App\Models\Video;

/** Cadastro de perguntas frequentes e dos vínculos com outros conteúdos (somente administradores). */
final class FaqController extends Controller
{
    /** Campo do formulário => tipo de vínculo. */
    private const CAMPOS_VINCULO = [
        'vinculo_video' => 'video',
        'vinculo_material' => 'material',
        'vinculo_legislacao' => 'legislacao',
        'vinculo_contato' => 'contato',
    ];

    public function index(): void
    {
        $this->exigirPerfil('admin');

        $this->render('admin/faq/index', [
            'titulo' => 'Perguntas frequentes',
            'perguntas' => (new Faq())->listarParaAdmin(),
        ]);
    }

    public function create(): void
    {
        $this->exigirPerfil('admin');

        $this->renderizarFormulario('Nova pergunta', [
            'categoria' => 'cadastro', 'pergunta' => '', 'resposta' => '', 'ordem' => '', 'ativo' => '1',
        ], array_fill_keys(Faq::TIPOS_VINCULO, []));
    }

    public function store(): void
    {
        $admin = $this->exigirPerfil('admin');

        $valores = $this->valoresDoFormulario();
        $vinculos = $this->lerVinculos();
        $validador = $this->validar($valores);

        if ($validador->falhou()) {
            $this->renderizarFormulario('Nova pergunta', $valores, $vinculos, $validador->erros());

            return;
        }

        $modelo = new Faq();

        $id = $modelo->criar([
            'categoria' => $valores['categoria'],
            'pergunta' => $valores['pergunta'],
            'resposta' => $valores['resposta'],
            'ordem' => $valores['ordem'] === '' ? $modelo->proximaOrdem($valores['categoria']) : (int) $valores['ordem'],
            'ativo' => (int) $valores['ativo'],
            'autor_id' => (int) $admin['id'],
        ]);
        $modelo->salvarVinculos($id, $vinculos);

        if ($valores['ativo'] === '1') {
            (new Notificacao())->criar('faq', 'Nova pergunta frequente: ' . $valores['pergunta'], $id);
        }

        $this->auditar('faq.criada', 'faqs', $id, 'Pergunta cadastrada: ' . $valores['pergunta']);
        FlashMessage::sucesso('Pergunta cadastrada.');
        $this->redirecionar('/admin/faq');
    }

    public function edit(): void
    {
        $this->exigirPerfil('admin');

        $pergunta = $this->localizar($this->idGet());

        $this->renderizarFormulario('Editar pergunta', [
            'categoria' => $pergunta['categoria'],
            'pergunta' => $pergunta['pergunta'],
            'resposta' => $pergunta['resposta'],
            'ordem' => (string) $pergunta['ordem'],
            'ativo' => (string) $pergunta['ativo'],
        ], (new Faq())->vinculosIds((int) $pergunta['id']), [], $pergunta);
    }

    public function update(): void
    {
        $this->exigirPerfil('admin');

        $pergunta = $this->localizar($this->idPost());
        $id = (int) $pergunta['id'];

        $valores = $this->valoresDoFormulario();
        $vinculos = $this->lerVinculos();
        $validador = $this->validar($valores);

        if ($validador->falhou()) {
            $this->renderizarFormulario('Editar pergunta', $valores, $vinculos, $validador->erros(), $pergunta);

            return;
        }

        $modelo = new Faq();

        $modelo->atualizar($id, [
            'categoria' => $valores['categoria'],
            'pergunta' => $valores['pergunta'],
            'resposta' => $valores['resposta'],
            'ordem' => $valores['ordem'] === '' ? (int) $pergunta['ordem'] : (int) $valores['ordem'],
            'ativo' => (int) $valores['ativo'],
        ]);
        $modelo->salvarVinculos($id, $vinculos);

        // Só avisa os gestores quando a pergunta passa a ficar visível.
        if ((int) $pergunta['ativo'] === 0 && $valores['ativo'] === '1') {
            (new Notificacao())->criar('faq', 'Nova pergunta frequente: ' . $valores['pergunta'], $id);
        }

        $this->auditar('faq.atualizada', 'faqs', $id, 'Pergunta atualizada: ' . $valores['pergunta']);
        FlashMessage::sucesso('Pergunta atualizada.');
        $this->redirecionar('/admin/faq');
    }

    /** @return array<string, mixed> */
    private function localizar(int $id): array
    {
        $pergunta = $id > 0 ? (new Faq())->buscarPorId($id) : null;

        if ($pergunta === null) {
            throw new HttpException(404, 'Pergunta não encontrada.');
        }

        return $pergunta;
    }

    /** @return array<string, string> */
    private function valoresDoFormulario(): array
    {
        return [
            'categoria' => $this->post('categoria'),
            'pergunta' => $this->post('pergunta'),
            'resposta' => $this->post('resposta'),
            'ordem' => $this->post('ordem'),
            'ativo' => $this->post('ativo') === '1' ? '1' : '0',
        ];
    }

    private function validar(array $valores): Validator
    {
        return (new Validator($valores))
            ->opcao('categoria', 'uma categoria', array_keys(Rotulos::CATEGORIAS))
            ->obrigatorio('pergunta', 'a pergunta')->tamanho('pergunta', 'a pergunta', 5, 255)
            ->obrigatorio('resposta', 'a resposta')->tamanho('resposta', 'a resposta', 10, 5000)
            ->inteiro('ordem', 'a ordem', 1, 9999);
    }

    /**
     * Lê os vínculos marcados e descarta ids que não existem (formulário adulterado).
     *
     * @return array<string, list<int>>
     */
    private function lerVinculos(): array
    {
        $opcoes = $this->opcoesDeVinculo();
        $resultado = array_fill_keys(Faq::TIPOS_VINCULO, []);

        foreach (self::CAMPOS_VINCULO as $campo => $tipo) {
            $enviados = $_POST[$campo] ?? [];

            if (!is_array($enviados)) {
                continue;
            }

            foreach ($enviados as $valor) {
                $id = filter_var($valor, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

                if ($id !== false && isset($opcoes[$tipo][$id])) {
                    $resultado[$tipo][$id] = $id;
                }
            }

            $resultado[$tipo] = array_values($resultado[$tipo]);
        }

        return $resultado;
    }

    /**
     * Conteúdos que podem ser vinculados, no formato [tipo => [id => título]].
     *
     * @return array<string, array<int, string>>
     */
    private function opcoesDeVinculo(): array
    {
        $mapa = static function (array $linhas, string $campo): array {
            $resultado = [];

            foreach ($linhas as $linha) {
                $resultado[(int) $linha['id']] = (string) $linha[$campo];
            }

            return $resultado;
        };

        return [
            'video' => $mapa((new Video())->listarParaAdmin(), 'titulo'),
            'material' => $mapa((new Material())->listarParaAdmin(), 'titulo'),
            'legislacao' => $mapa((new Legislacao())->listarParaAdmin(), 'titulo'),
            'contato' => $mapa((new Contato())->listarTodos(), 'nome'),
        ];
    }

    private function renderizarFormulario(
        string $titulo,
        array $valores,
        array $vinculos,
        array $erros = [],
        ?array $pergunta = null
    ): void {
        if ($erros !== []) {
            http_response_code(422);
        }

        $this->render('admin/faq/form', [
            'titulo' => $titulo,
            'valores' => $valores,
            'vinculos' => $vinculos,
            'erros' => $erros,
            'pergunta' => $pergunta,
            'opcoes' => $this->opcoesDeVinculo(),
        ]);
    }
}
