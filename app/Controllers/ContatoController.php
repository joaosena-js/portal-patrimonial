<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Contato;

/** Página de contatos da CPAT, do DBM e do DBI. */
final class ContatoController extends Controller
{
    public function index(): void
    {
        $this->exigirLogin();

        $this->render('contatos/index', [
            'titulo' => 'Contatos',
            'contatos' => (new Contato())->listarTodos(),
        ]);
    }
}
