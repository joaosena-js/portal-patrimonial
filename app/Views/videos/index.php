<?= migalhas([['Vídeos operacionais']]) ?>

<div class="cabecalho-pagina">
    <div>
        <h1 class="h3 mb-1">Vídeos operacionais</h1>
        <p class="text-secondary mb-0">Passo a passo das rotinas patrimoniais e do sistema AJURI.</p>
    </div>
</div>

<form method="get" action="<?= e(url('/videos')) ?>" class="painel filtros mb-4" role="search">
    <div class="row g-2 align-items-end">
        <div class="col-md-5">
            <label for="q" class="form-label">Buscar</label>
            <input type="search" class="form-control" id="q" name="q" value="<?= e($filtros['q']) ?>" maxlength="100" placeholder="Título ou descrição">
        </div>
        <div class="col-md-3">
            <label for="categoria" class="form-label">Categoria</label>
            <select class="form-select" id="categoria" name="categoria">
                <option value="">Todas</option>
                <?php foreach (\App\Helpers\Rotulos::CATEGORIAS as $chave => $texto) : ?>
                    <option value="<?= e($chave) ?>"<?= $filtros['categoria'] === $chave ? ' selected' : '' ?>><?= e($texto) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label for="ordem" class="form-label">Ordenar por</label>
            <select class="form-select" id="ordem" name="ordem">
                <option value="recentes"<?= $filtros['ordem'] === 'recentes' ? ' selected' : '' ?>>Mais recentes</option>
                <option value="antigos"<?= $filtros['ordem'] === 'antigos' ? ' selected' : '' ?>>Mais antigos</option>
                <option value="titulo"<?= $filtros['ordem'] === 'titulo' ? ' selected' : '' ?>>Título</option>
            </select>
        </div>
        <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-primario">Filtrar</button>
            <a class="btn btn-link" href="<?= e(url('/videos')) ?>">Limpar</a>
        </div>
    </div>
</form>

<?php if ($videos === []) : ?>
    <p class="text-secondary">Nenhum vídeo encontrado.</p>
<?php endif; ?>

<div class="row g-3">
    <?php foreach ($videos as $video) : ?>
        <div class="col-md-6 col-lg-4">
            <article class="painel cartao-video h-100">
                <div class="d-flex flex-wrap gap-1 mb-2">
                    <span class="badge text-bg-light border"><?= e(rotulo('CATEGORIAS', $video['categoria'])) ?></span>
                    <span class="badge text-bg-light border"><?= e(rotulo('AREAS_RESPONSAVEIS', $video['area_responsavel'])) ?></span>
                    <?php if (eh_recente($video['publicado_em'])) : ?>
                        <span class="badge text-bg-success">Novo</span>
                    <?php endif; ?>
                </div>
                <h2 class="h6 mb-2">
                    <a class="stretched-link" href="<?= e(url('/videos/ver', ['id' => (int) $video['id']])) ?>">
                        <i class="bi bi-play-circle" aria-hidden="true"></i> <?= e($video['titulo']) ?>
                    </a>
                </h2>
                <?php if (!empty($video['descricao'])) : ?>
                    <p class="small text-secondary mb-2"><?= e(mb_strimwidth((string) $video['descricao'], 0, 130, '...')) ?></p>
                <?php endif; ?>
                <div class="small text-secondary mt-auto">Publicado em <?= e(data_br($video['publicado_em'])) ?></div>
            </article>
        </div>
    <?php endforeach; ?>
</div>
