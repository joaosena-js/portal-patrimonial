<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Auditoria;
use App\Models\Aviso;
use App\Models\Inscricao;
use App\Models\Material;
use App\Models\OrgaoUnidade;
use App\Models\Trilha;
use App\Models\Usuario;
use App\Models\Video;

/** Páginas iniciais de cada perfil. */
final class DashboardController extends Controller
{
    public function admin(): void
    {
        $this->exigirPerfil('admin');

        $usuarios = new Usuario();
        $inscricoes = new Inscricao();
        $totais = $inscricoes->totais();
        $totaisUsuarios = $usuarios->contarTodos();

        $this->render('admin/dashboard', [
            'titulo' => 'Painel administrativo',
            'resumo' => [
                'usuarios_cadastrados' => $totaisUsuarios['total'],
                'usuarios_ativos' => $totaisUsuarios['ativos'],
                'orgaos' => (new OrgaoUnidade())->contarAtivos(),
                'videos' => (new Video())->contarAtivos(),
                'materiais' => (new Material())->contarAtivos(),
                'avisos_ativos' => (new Aviso())->contarVisiveis(),
                'gestores_pendentes' => $usuarios->contar('gestor', 'pendente'),
                'trilhas_publicadas' => (new Trilha())->contarPorSituacao('publicada'),
                'inscricoes' => $totais['inscricoes'],
                'concluidas' => $totais['concluidas'],
            ],
            'acompanhamento' => $inscricoes->resumoPorTrilha(),
            'atividades' => (new Auditoria())->recentes(8),
            'avisos' => (new Aviso())->listarVisiveis(3),
        ]);
    }

    public function gestor(): void
    {
        $usuario = $this->exigirPerfil('gestor');

        $trilhas = (new Trilha())->listarPublicadasParaGestor((int) $usuario['id']);

        $minhasTrilhas = array_values(array_filter(
            $trilhas,
            static fn (array $trilha): bool => $trilha['inscricao_situacao'] !== null
        ));

        $this->render('gestor/dashboard', [
            'titulo' => 'Início',
            'minhasTrilhas' => $minhasTrilhas,
            'trilhasDisponiveis' => count($trilhas) - count($minhasTrilhas),
            'avisos' => (new Aviso())->listarVisiveis(3),
        ]);
    }
}
