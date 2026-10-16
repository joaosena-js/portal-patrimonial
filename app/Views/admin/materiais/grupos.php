<?= migalhas([['Materiais de apoio', '/admin/materiais'], ['Assuntos']]) ?>

<div class="cabecalho-pagina">
    <div>
        <h1 class="h3 mb-1">Assuntos dos materiais</h1>
        <p class="text-secondary mb-0">Cada assunto vira uma seção do accordion (ex.: Transferência entre Órgãos, Inventário Patrimonial).</p>
    </div>
    <a class="btn btn-primario" href="<?= e(url('/admin/materiais/grupos/cadastrar')) ?>">Novo assunto</a>
</div>

<section class="painel p-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th class="text-end">Ordem</th><th>Assunto</th><th class="text-end">Materiais</th><th>Situação</th><th><span class="visually-hidden">Ações</span></th></tr></thead>
            <tbody>
            <?php if ($grupos === []) : ?>
                <tr><td colspan="5" class="text-center text-secondary py-4">Nenhum assunto cadastrado.</td></tr>
            <?php endif; ?>
            <?php foreach ($grupos as $grupo) : ?>
                <tr>
                    <td class="text-end"><?= (int) $grupo['ordem'] ?></td>
                    <td class="fw-semibold"><?= e($grupo['titulo']) ?></td>
                    <td class="text-end"><?= (int) $grupo['total_materiais'] ?></td>
                    <td><span class="badge <?= (int) $grupo['ativo'] === 1 ? 'text-bg-success' : 'text-bg-secondary' ?>"><?= (int) $grupo['ativo'] === 1 ? 'Visível' : 'Oculto' ?></span></td>
                    <td class="text-end text-nowrap">
                        <a class="btn btn-sm btn-outline-primario" href="<?= e(url('/admin/materiais/cadastrar', ['grupo_id' => (int) $grupo['id']])) ?>">Novo material</a>
                        <a class="btn btn-sm btn-outline-primario" href="<?= e(url('/admin/materiais/grupos/editar', ['id' => (int) $grupo['id']])) ?>">Editar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
