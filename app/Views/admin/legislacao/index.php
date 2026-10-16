<?= migalhas([['Legislação e normativos']]) ?>

<div class="cabecalho-pagina">
    <h1 class="h3 mb-0">Legislação e normativos</h1>
    <div class="d-flex gap-2">
        <a class="btn btn-outline-primario" href="<?= e(url('/legislacao')) ?>">Ver como gestor</a>
        <a class="btn btn-primario" href="<?= e(url('/admin/legislacao/cadastrar')) ?>">Novo documento</a>
    </div>
</div>

<section class="painel p-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>Categoria</th><th>Documento</th><th>Publicação</th><th>Situação</th><th>Exibição</th><th><span class="visually-hidden">Ações</span></th></tr></thead>
            <tbody>
            <?php if ($documentos === []) : ?>
                <tr><td colspan="6" class="text-center text-secondary py-4">Nenhum documento cadastrado.</td></tr>
            <?php endif; ?>
            <?php foreach ($documentos as $doc) : ?>
                <tr>
                    <td><?= e(rotulo('CATEGORIAS_LEGISLACAO', $doc['categoria'])) ?></td>
                    <td>
                        <div class="fw-semibold"><?= e($doc['titulo']) ?></div>
                        <?php if (!empty($doc['numero'])) : ?><div class="small text-secondary"><?= e($doc['numero']) ?></div><?php endif; ?>
                    </td>
                    <td><?= e(data_br($doc['data_publicacao'])) ?></td>
                    <td><span class="badge <?= $doc['situacao'] === 'vigente' ? 'text-bg-success' : 'text-bg-secondary' ?>"><?= e(rotulo('SITUACOES_LEGISLACAO', $doc['situacao'])) ?></span></td>
                    <td><span class="badge <?= (int) $doc['ativo'] === 1 ? 'text-bg-success' : 'text-bg-secondary' ?>"><?= (int) $doc['ativo'] === 1 ? 'Visível' : 'Oculto' ?></span></td>
                    <td class="text-end"><a class="btn btn-sm btn-outline-primario" href="<?= e(url('/admin/legislacao/editar', ['id' => (int) $doc['id']])) ?>">Editar</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
