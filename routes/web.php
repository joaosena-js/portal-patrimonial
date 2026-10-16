<?php

declare(strict_types=1);

/**
 * Mapa de rotas: método HTTP => caminho => [Controller, método].
 * Controllers da área administrativa ficam em App\Controllers\Admin (prefixo "Admin\").
 * Rotas POST passam obrigatoriamente pela validação do token CSRF.
 * O controle de perfil é feito dentro de cada ação (exigirPerfil), nunca apenas no menu.
 */
return [
    'GET' => [
        // Público
        '/' => ['AuthController', 'mostrarLogin'],
        '/login' => ['AuthController', 'mostrarLogin'],
        '/cadastro' => ['CadastroController', 'mostrar'],

        // Qualquer usuário autenticado
        '/conta/senha' => ['AuthController', 'mostrarTrocaSenha'],
        '/perfil' => ['PerfilController', 'index'],
        '/avisos' => ['AvisoController', 'index'],
        '/videos' => ['VideoController', 'index'],
        '/videos/ver' => ['VideoController', 'ver'],
        '/videos/arquivo' => ['VideoController', 'arquivo'],
        '/materiais' => ['MaterialController', 'index'],
        '/materiais/baixar' => ['MaterialController', 'baixar'],
        '/legislacao' => ['LegislacaoController', 'index'],
        '/legislacao/baixar' => ['LegislacaoController', 'baixar'],
        '/contatos' => ['ContatoController', 'index'],
        '/faq' => ['FaqController', 'index'],
        '/busca' => ['BuscaController', 'index'],
        '/notificacoes' => ['NotificacaoController', 'index'],

        // Administração
        '/admin' => ['DashboardController', 'admin'],
        '/admin/usuarios' => ['UsuarioController', 'index'],
        '/admin/usuarios/cadastrar' => ['UsuarioController', 'create'],
        '/admin/usuarios/editar' => ['UsuarioController', 'edit'],
        '/admin/orgaos' => ['Admin\OrgaoController', 'index'],
        '/admin/orgaos/cadastrar' => ['Admin\OrgaoController', 'create'],
        '/admin/orgaos/editar' => ['Admin\OrgaoController', 'edit'],
        '/admin/trilhas' => ['TrilhaController', 'index'],
        '/admin/trilhas/cadastrar' => ['TrilhaController', 'create'],
        '/admin/trilhas/editar' => ['TrilhaController', 'edit'],
        '/admin/modulos' => ['ModuloController', 'index'],
        '/admin/modulos/cadastrar' => ['ModuloController', 'create'],
        '/admin/modulos/editar' => ['ModuloController', 'edit'],
        '/admin/videos' => ['Admin\VideoController', 'index'],
        '/admin/videos/cadastrar' => ['Admin\VideoController', 'create'],
        '/admin/videos/editar' => ['Admin\VideoController', 'edit'],
        '/admin/materiais' => ['Admin\MaterialController', 'index'],
        '/admin/materiais/cadastrar' => ['Admin\MaterialController', 'create'],
        '/admin/materiais/editar' => ['Admin\MaterialController', 'edit'],
        '/admin/materiais/grupos' => ['Admin\MaterialController', 'gruposIndex'],
        '/admin/materiais/grupos/cadastrar' => ['Admin\MaterialController', 'grupoCreate'],
        '/admin/materiais/grupos/editar' => ['Admin\MaterialController', 'grupoEdit'],
        '/admin/legislacao' => ['Admin\LegislacaoController', 'index'],
        '/admin/legislacao/cadastrar' => ['Admin\LegislacaoController', 'create'],
        '/admin/legislacao/editar' => ['Admin\LegislacaoController', 'edit'],
        '/admin/faq' => ['Admin\FaqController', 'index'],
        '/admin/faq/cadastrar' => ['Admin\FaqController', 'create'],
        '/admin/faq/editar' => ['Admin\FaqController', 'edit'],
        '/admin/contatos' => ['Admin\ContatoController', 'index'],
        '/admin/contatos/editar' => ['Admin\ContatoController', 'edit'],
        '/admin/avisos/cadastrar' => ['AvisoController', 'create'],

        // Gestor patrimonial
        '/gestor' => ['DashboardController', 'gestor'],
        '/gestor/trilhas' => ['TrilhaController', 'catalogo'],
        '/gestor/trilha' => ['TrilhaController', 'show'],
        '/gestor/modulo' => ['ModuloController', 'show'],
    ],

    'POST' => [
        '/login' => ['AuthController', 'login'],
        '/logout' => ['AuthController', 'logout'],
        '/cadastro/salvar' => ['CadastroController', 'salvar'],
        '/conta/senha' => ['AuthController', 'trocarSenha'],
        '/perfil/atualizar' => ['PerfilController', 'atualizar'],
        '/notificacoes/adiar' => ['NotificacaoController', 'adiar'],

        '/admin/usuarios/salvar' => ['UsuarioController', 'store'],
        '/admin/usuarios/atualizar' => ['UsuarioController', 'update'],
        '/admin/usuarios/aprovar' => ['UsuarioController', 'aprovar'],
        '/admin/usuarios/rejeitar' => ['UsuarioController', 'rejeitar'],
        '/admin/orgaos/salvar' => ['Admin\OrgaoController', 'store'],
        '/admin/orgaos/atualizar' => ['Admin\OrgaoController', 'update'],
        '/admin/trilhas/salvar' => ['TrilhaController', 'store'],
        '/admin/trilhas/atualizar' => ['TrilhaController', 'update'],
        '/admin/modulos/salvar' => ['ModuloController', 'store'],
        '/admin/modulos/atualizar' => ['ModuloController', 'update'],
        '/admin/videos/salvar' => ['Admin\VideoController', 'store'],
        '/admin/videos/atualizar' => ['Admin\VideoController', 'update'],
        '/admin/materiais/salvar' => ['Admin\MaterialController', 'store'],
        '/admin/materiais/atualizar' => ['Admin\MaterialController', 'update'],
        '/admin/materiais/grupos/salvar' => ['Admin\MaterialController', 'grupoStore'],
        '/admin/materiais/grupos/atualizar' => ['Admin\MaterialController', 'grupoUpdate'],
        '/admin/legislacao/salvar' => ['Admin\LegislacaoController', 'store'],
        '/admin/legislacao/atualizar' => ['Admin\LegislacaoController', 'update'],
        '/admin/faq/salvar' => ['Admin\FaqController', 'store'],
        '/admin/faq/atualizar' => ['Admin\FaqController', 'update'],
        '/admin/contatos/atualizar' => ['Admin\ContatoController', 'update'],
        '/admin/avisos/salvar' => ['AvisoController', 'store'],
        '/admin/avisos/desativar' => ['AvisoController', 'desativar'],

        '/gestor/trilha/inscrever' => ['TrilhaController', 'inscrever'],
        '/gestor/modulo/concluir' => ['ProgressoController', 'concluir'],
    ],
];
