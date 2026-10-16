<nav class="mb-3">
    <a class="small" href="<?= e(url('/gestor/trilha', ['id' => (int) $trilha['id']])) ?>">Voltar à trilha: <?= e($trilha['titulo']) ?></a>
</nav>

<div class="cabecalho-pagina">
    <div>
        <div class="text-secondary small">Módulo <?= (int) $posicao ?> de <?= (int) $totalModulos ?></div>
        <h1 class="h3 mb-0"><?= e($modulo['titulo']) ?></h1>
    </div>
    <?php if ($concluido) : ?>
        <span class="badge text-bg-success fs-6">Concluído</span>
    <?php endif; ?>
</div>

<?php if (!empty($modulo['descricao'])) : ?>
    <p class="texto-leitura text-secondary"><?= e($modulo['descricao']) ?></p>
<?php endif; ?>

<section class="painel mb-4">
    <?php if ($modulo['tipo'] === 'texto') : ?>
        <div class="texto-leitura"><?= nl2br(e($modulo['conteudo_texto'])) ?></div>
    <?php else : ?>
        <p class="mb-3">
            Este conteúdo é do tipo <strong><?= e(mb_strtolower(rotulo('TIPOS_MODULO', $modulo['tipo']))) ?></strong>
            e abre em uma nova aba.
        </p>
        <a class="btn btn-primario" href="<?= e(url_segura($modulo['conteudo_url'])) ?>" target="_blank" rel="noopener noreferrer">
            Abrir conteúdo
        </a>
    <?php endif; ?>
</section>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
        <?php if ($anterior !== null) : ?>
            <a class="btn btn-outline-primario" href="<?= e(url('/gestor/modulo', ['id' => (int) $anterior['id']])) ?>">Módulo anterior</a>
        <?php endif; ?>
    </div>

    <div class="d-flex gap-2">
        <?php if (!$concluido) : ?>
            <form method="post" action="<?= e(url('/gestor/modulo/concluir')) ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="modulo_id" value="<?= (int) $modulo['id'] ?>">
                <button type="submit" class="btn btn-primario">Marcar como concluído</button>
            </form>
        <?php endif; ?>
        <?php if ($proximo !== null) : ?>
            <a class="btn btn-outline-primario" href="<?= e(url('/gestor/modulo', ['id' => (int) $proximo['id']])) ?>">Próximo módulo</a>
        <?php endif; ?>
    </div>
</div>
