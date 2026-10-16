<?php

declare(strict_types=1);

class HomeController extends Controller
{
    public function index(): void
    {
        $dados = [
            'titulo' => 'Portal de Capacitação Patrimonial',
            'mensagem' => 'Estrutura MVC configurada com sucesso.',
        ];

        $this->view('home/index', $dados);
    }
}