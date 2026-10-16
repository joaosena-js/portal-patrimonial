<?php
/** Resultados da pesquisa global. Cada seção mostra até $porSecao itens e um link para ver todos. */
$termoUrl = ['q' => $termo];
?>
<?= migalhas([['Pesquisa']]) ?>

<div class="cabecalho-pagina">
    <div>
        <h1 class="h3 mb-1">Pesquisa</h1>
        <?php if ($termo !== '' && !$termoCurto) : ?>
            <p class="text-secondary mb-0"><?= (int) $total ?> resultado(s) para <strong><?= e($termo) ?></strong></p>
        <?php endif; ?>
    </div>
</div>

<?php if ($termo === '' || $termoCurto) : ?>
    <section class="painel">
        <p class="mb-0 text-secondary">Digite ao menos 2 letras no campo de pesquisa do topo da página.</p>
    </section>
<?php elseif ($total === 0) : ?>
    <section class="painel">
        <p class="mb-0 text-secondary">Nada encontrado. Tente uma palavra mais curta ou um termo diferente.</p>
    </section>
<?php else : ?>
    <?php
    $blocos = [
        'trilhas' => ['Trilhas', 'bi-mortarboard', null],
        'videos' => ['Vídeos operacionais', 'bi-camera-video', '/videos'],
        'materiais' => ['Materiais de apoio', 'bi-journal-text', '/materiais'],
        'legislacao' => ['Legislação e normativos', 'bi-bank', '/legislacao'],
        'faq' => ['Perguntas frequentes', 'bi-question-circle', '/faq'],
        'avisos' => ['Avisos', 'bi-megaphone', '/avisos'],
    ];
    ?>
    <?php foreach ($blocos as $chave => [$rotuloSecao, $icone, $verTodos]) : ?>
        <?php $itens = $secoes[$chave] ?? []; ?>
        <?php if ($itens === []) : ?>
            <?php continue; ?>
        <?php endif; ?>

        <section class="mb-4">
            <div class="d-flex justify-content-between align-items-baseline mb-2">
                <h2 class="h5 mb-0"><i class="bi <?= e($icone) ?>" aria-hidden="true"></i> <?= e($rotuloSecao) ?> <span class="text-secondary fw-normal">(<?= count($itens) ?>)</span></h2>
                <?php if ($verTodos !== null && count($itens) > $porSecao) : ?>
                    <a class="small" href="<?= e(url($verTodos, $termoUrl)) ?>">Ver todos</a>
                <?php endif; ?>
            </div>

            <div class="painel p-0">
                <?php foreach (array_slice($itens, 0, $porSecao) as $item) : ?>
                    <div class="resultado-linha">
                        <i class="bi <?= e($icone) ?>" aria-hidden="true"></i>
                        <div>
                            <?php if ($chave === 'trilhas') : ?>
                                <a class="fw-semibold" href="<?= e($ehAdmin ? url('/admin/trilhas/editar', ['id' => (int) $item['id']]) : url('/gestor/trilha', ['id' => (int) $item['id']])) ?>"><?= e($item['titulo']) ?></a>
                                <div class="small text-secondary"><?= e(rotulo('AREAS', $item['area'])) ?></div>
                            <?php elseif ($chave === 'videos') : ?>
                                <a class="fw-semibold" href="<?= e(url('/videos/ver', ['id' => (int) $item['id']])) ?>"><?= e($item['titulo']) ?></a>
                                <div class="small text-secondary"><?= e(rotulo('CATEGORIAS', $item['categoria'])) ?></div>
                            <?php elseif ($chave === 'materiais') : ?>
                                <a class="fw-semibold" href="<?= e(url('/materiais/baixar', ['id' => (int) $item['id']])) ?>"><?= e($item['titulo']) ?></a>
                                <div class="small text-secondary"><?= e(rotulo('TIPOS_MATERIAL', $item['tipo'])) ?> · <?= e($item['grupo_titulo']) ?></div>
                            <?php elseif ($chave === 'legislacao') : ?>
                                <?php
                                $destino = $item['arquivo_id'] !== null
                                    ? url('/legislacao/baixar', ['id' => (int) $item['id']])
                                    : (!empty($item['url']) ? url_segura($item['url']) : url('/legislacao', $termoUrl));
                                ?>
                                <a class="fw-semibold" href="<?= e($destino) ?>"><?= e($item['titulo']) ?></a>
                                <div class="small text-secondary">
                                    <?= e(rotulo('CATEGORIAS_LEGISLACAO', $item['categoria'])) ?>
                                    <?php if (!empty($item['numero'])) : ?> · <?= e($item['numero']) ?><?php endif; ?>
                                </div>
                            <?php elseif ($chave === 'faq') : ?>
                                <a class="fw-semibold" href="<?= e(url('/faq', $termoUrl)) ?>"><?= e($item['pergunta']) ?></a>
                                <div class="small text-secondary"><?= e(rotulo('CATEGORIAS', $item['categoria'])) ?></div>
                            <?php else : ?>
                                <a class="fw-semibold" href="<?= e(url('/avisos')) ?>"><?= e($item['titulo']) ?></a>
                                <div class="small text-secondary">Publicado em <?= e(data_br($item['publicado_em'])) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endforeach; ?>
<?php endif; ?>
