<?= migalhas([['Vídeos operacionais']]) ?>

<div class="cabecalho-pagina">
    <h1 class="h3 mb-0">Vídeos operacionais</h1>
    <div class="d-flex gap-2">
        <a class="btn btn-outline-primario" href="<?= e(url('/videos')) ?>">Ver como gestor</a>
        <a class="btn btn-primario" href="<?= e(url('/admin/videos/cadastrar')) ?>">Novo vídeo</a>
    </div>
</div>

<section class="painel p-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>Título</th><th>Categoria</th><th>Área</th><th>Origem</th><th>Publicação</th><th>Situação</th><th><span class="visually-hidden">Ações</span></th></tr></thead>
            <tbody>
            <?php if ($videos === []) : ?>
                <tr><td colspan="7" class="text-center text-secondary py-4">Nenhum vídeo cadastrado.</td></tr>
            <?php endif; ?>
            <?php foreach ($videos as $video) : ?>
                <tr>
                    <td class="fw-semibold"><?= e($video['titulo']) ?></td>
                    <td><?= e(rotulo('CATEGORIAS', $video['categoria'])) ?></td>
                    <td><?= e(rotulo('AREAS_RESPONSAVEIS', $video['area_responsavel'])) ?></td>
                    <td><?= e(rotulo('ORIGENS_VIDEO', $video['origem'])) ?></td>
                    <td><?= e(data_br($video['publicado_em'])) ?></td>
                    <td><span class="badge <?= (int) $video['ativo'] === 1 ? 'text-bg-success' : 'text-bg-secondary' ?>"><?= (int) $video['ativo'] === 1 ? 'Visível' : 'Oculto' ?></span></td>
                    <td class="text-end"><a class="btn btn-sm btn-outline-primario" href="<?= e(url('/admin/videos/editar', ['id' => (int) $video['id']])) ?>">Editar</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
