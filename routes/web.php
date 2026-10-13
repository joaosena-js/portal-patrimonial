<?php

return [
    'GET' => [
        '/' => ['AuthController', 'mostrarLogin'],
        '/login' => ['AuthController', 'mostrarLogin'],
        '/logout' => ['AuthController', 'logout'],

        '/admin' => ['DashboardController', 'admin'],
        '/admin/usuarios' => ['UsuarioController', 'index'],
        '/admin/usuarios/cadastrar' => ['UsuarioController', 'create'],

        '/admin/trilhas' => ['TrilhaController', 'index'],
        '/admin/trilhas/cadastrar' => ['TrilhaController', 'create'],

        '/gestor' => ['DashboardController', 'gestor'],
        '/gestor/trilhas' => ['TrilhaController', 'catalogo'],
        '/gestor/trilha' => ['TrilhaController', 'show'],
        '/gestor/modulo' => ['ModuloController', 'show'],
    ],

    'POST' => [
        '/login' => ['AuthController', 'login'],
        '/admin/usuarios/salvar' => ['UsuarioController', 'store'],
        '/admin/trilhas/salvar' => ['TrilhaController', 'store'],
        '/gestor/modulo/concluir' => ['ProgressoController', 'concluir'],
    ],
];