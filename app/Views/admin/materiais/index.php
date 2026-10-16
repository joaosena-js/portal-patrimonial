<?= migalhas([['Materiais de apoio']]) ?>

<div class="cabecalho-pagina">
    <h1 class="h3 mb-0">Materiais de apoio</h1>
    <div class="d-flex flex-wrap gap-2">
        <a class="btn btn-outline-primario" href="<?= e(url('/materiais')) ?>">Ver como gestor</a>
        <a class="btn btn-outline-primario" href="<?= e(url('/admin/materiais/grupos')) ?>">Gerenciar assuntos</a>
        <a class="btn btn-primario" href="<?= e(url('/admin/materiais/cadastrar')) ?>">Novo material</a>
    </div>
</div>

<section class="painel p-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>Assunto</th><th>Material</th><th>Tipo</th><th>Arquivo</th><th>Publicação</th><th>Situação</th><th><span class="visually-hidden">Ações</span></th></tr></thead>
            <tbody>
            <?php if ($materiais === []) : ?>
                <tr><td colspan="7" class="text-center text-secondary py-4">Nenhum material cadastrado. Crie primeiro um assunto (ex.: Transferência entre Órgãos).</td></tr>
            <?php endif; ?>
            <?php foreach ($materiais as $material) : ?>
                <tr>
                    <td><?= e($material['grupo_titulo']) ?></td>
                    <td class="fw-semibold"><?= e($material['titulo']) ?></td>
                    <td><?= e(rotulo('TIPOS_MATERIAL', $material['tipo'])) ?></td>
                    <td><?= e(strtoupper((string) $material['arquivo_extensao'])) ?> · <?= e(tamanho_legivel((int) $material['arquivo_tamanho'])) ?></td>
                    <td><?= e(data_br($material['publicado_em'])) ?></td>
                    <td><span class="badge <?= (int) $material['ativo'] === 1 ? 'text-bg-success' : 'text-bg-secondary' ?>"><?= (int) $material['ativo'] === 1 ? 'Visível' : 'Oculto' ?></span></td>
                    <td class="text-end"><a class="btn btn-sm btn-outline-primario" href="<?= e(url('/admin/materiais/editar', ['id' => (int) $material['id']])) ?>">Editar</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
