<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Aviso;
use App\Models\Faq;
use App\Models\Legislacao;
use App\Models\Material;
use App\Models\Trilha;
use App\Models\Video;

/** Pesquisa global: trilhas, vídeos, materiais, legislação, perguntas frequentes e avisos. */
final class BuscaController extends Controller
{
    /** Resultados exibidos por seção. A seção traz um link para ver todos na página do módulo. */
    private const POR_SECAO = 6;

    public function index(): void
    {
        $usuario = $this->exigirLogin();

        $termo = mb_substr($this->get('q'), 0, 100);
        $secoes = [];

        if (mb_strlen($termo) >= 2) {
            $secoes = [
                'trilhas' => (new Trilha())->buscarPublicadas($termo, 50),
                'videos' => (new Video())->listarPublicos($termo, '', 'recentes'),
                'materiais' => (new Material())->listarPublicos($termo, ''),
                'legislacao' => (new Legislacao())->listarPublicos($termo, ''),
                'faq' => (new Faq())->listarPublicas($termo, ''),
                'avisos' => (new Aviso())->buscarTexto($termo, 50),
            ];
        }

        $total = array_sum(array_map('count', $secoes));

        $this->render('busca/index', [
            'titulo' => 'Pesquisa',
            'termo' => $termo,
            'termoCurto' => $termo !== '' && mb_strlen($termo) < 2,
            'secoes' => $secoes,
            'total' => $total,
            'porSecao' => self::POR_SECAO,
            'ehAdmin' => $usuario['perfil'] === 'admin',
        ]);
    }
}
