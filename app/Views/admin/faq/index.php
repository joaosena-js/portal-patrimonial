<?= migalhas([['Perguntas frequentes']]) ?>

<div class="cabecalho-pagina">
    <h1 class="h3 mb-0">Perguntas frequentes</h1>
    <div class="d-flex gap-2">
        <a class="btn btn-outline-primario" href="<?= e(url('/faq')) ?>">Ver como gestor</a>
        <a class="btn btn-primario" href="<?= e(url('/admin/faq/cadastrar')) ?>">Nova pergunta</a>
    </div>
</div>

<section class="painel p-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>Categoria</th><th>Pergunta</th><th class="text-end">Ordem</th><th>Exibição</th><th><span class="visually-hidden">Ações</span></th></tr></thead>
            <tbody>
            <?php if ($perguntas === []) : ?>
                <tr><td colspan="5" class="text-center text-secondary py-4">Nenhuma pergunta cadastrada.</td></tr>
            <?php endif; ?>
            <?php foreach ($perguntas as $p) : ?>
                <tr>
                    <td><?= e(rotulo('CATEGORIAS', $p['categoria'])) ?></td>
                    <td class="fw-semibold"><?= e($p['pergunta']) ?></td>
                    <td class="text-end"><?= (int) $p['ordem'] ?></td>
                    <td><span class="badge <?= (int) $p['ativo'] === 1 ? 'text-bg-success' : 'text-bg-secondary' ?>"><?= (int) $p['ativo'] === 1 ? 'Visível' : 'Oculta' ?></span></td>
                    <td class="text-end"><a class="btn btn-sm btn-outline-primario" href="<?= e(url('/admin/faq/editar', ['id' => (int) $p['id']])) ?>">Editar</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
