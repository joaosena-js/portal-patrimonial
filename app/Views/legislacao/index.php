<?= migalhas([['Legislação e normativos']]) ?>

<div class="cabecalho-pagina">
    <div>
        <h1 class="h3 mb-1">Legislação e normativos</h1>
        <p class="text-secondary mb-0">Fundamentação legal da gestão patrimonial e o assunto de cada documento.</p>
    </div>
</div>

<form method="get" action="<?= e(url('/legislacao')) ?>" class="painel filtros mb-4" role="search">
    <div class="row g-2 align-items-end">
        <div class="col-md-6">
            <label for="q" class="form-label">Pesquisar</label>
            <input type="search" class="form-control" id="q" name="q" value="<?= e($filtros['q']) ?>" maxlength="100" placeholder="Palavra do título, número ou resumo">
        </div>
        <div class="col-md-4">
            <label for="categoria" class="form-label">Categoria</label>
            <select class="form-select" id="categoria" name="categoria">
                <option value="">Todas</option>
                <?php foreach (\App\Helpers\Rotulos::CATEGORIAS_LEGISLACAO as $chave => $texto) : ?>
                    <option value="<?= e($chave) ?>"<?= $filtros['categoria'] === $chave ? ' selected' : '' ?>><?= e($texto) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-primario">Pesquisar</button>
            <a class="btn btn-link" href="<?= e(url('/legislacao')) ?>">Limpar</a>
        </div>
    </div>
</form>

<?php if ($documentos === []) : ?>
    <p class="text-secondary">Nenhum documento encontrado.</p>
<?php endif; ?>

<?php foreach ($documentos as $doc) : ?>
    <article class="painel mb-3">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
            <div>
                <div class="d-flex flex-wrap gap-1 mb-1">
                    <span class="badge text-bg-light border"><?= e(rotulo('CATEGORIAS_LEGISLACAO', $doc['categoria'])) ?></span>
                    <span class="badge <?= $doc['situacao'] === 'vigente' ? 'text-bg-success' : 'text-bg-secondary' ?>"><?= e(rotulo('SITUACOES_LEGISLACAO', $doc['situacao'])) ?></span>
                    <?php if (eh_recente($doc['criado_em'])) : ?>
                        <span class="badge text-bg-success">Novo</span>
                    <?php endif; ?>
                </div>
                <h2 class="h5 mb-1">
                    <?= e($doc['titulo']) ?>
                    <?php if (!empty($doc['numero'])) : ?><span class="text-secondary fw-normal">· <?= e($doc['numero']) ?></span><?php endif; ?>
                </h2>
                <?php if (!empty($doc['data_publicacao'])) : ?>
                    <div class="small text-secondary">Publicado em <?= e(data_br($doc['data_publicacao'])) ?></div>
                <?php endif; ?>
            </div>
            <div class="d-flex gap-2">
                <?php if ($doc['arquivo_id'] !== null) : ?>
                    <a class="btn btn-sm btn-outline-primario" href="<?= e(url('/legislacao/baixar', ['id' => (int) $doc['id']])) ?>">
                        <i class="bi bi-download" aria-hidden="true"></i> Baixar (<?= e(strtoupper((string) $doc['arquivo_extensao'])) ?>)
                    </a>
                <?php endif; ?>
                <?php if (!empty($doc['url'])) : ?>
                    <a class="btn btn-sm btn-outline-primario" href="<?= e(url_segura($doc['url'])) ?>" target="_blank" rel="noopener noreferrer">
                        <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i> Abrir link
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <?php if (!empty($doc['ementa'])) : ?>
            <p class="mb-0 mt-2"><strong>Do que trata:</strong> <?= e($doc['ementa']) ?></p>
        <?php endif; ?>
    </article>
<?php endforeach; ?>
