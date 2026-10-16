<?= migalhas([['Novidades']]) ?>

<div class="cabecalho-pagina">
    <div>
        <h1 class="h3 mb-1">Novidades</h1>
        <p class="text-secondary mb-0">Conteúdos publicados nos últimos 60 dias, desde o seu cadastro.</p>
    </div>
</div>

<?php if ($itens === []) : ?>
    <section class="painel"><p class="text-secondary mb-0">Nenhuma novidade no período.</p></section>
<?php else : ?>
    <section class="painel p-0">
        <ul class="lista-novidades">
            <?php foreach ($itens as $item) : ?>
                <li class="novidade-linha">
                    <i class="bi <?= e(icone_notificacao($item['tipo'])) ?> novidade-icone" aria-hidden="true"></i>
                    <div class="flex-grow-1">
                        <a class="fw-semibold" href="<?= e(url_notificacao($item['tipo'], $item['entidade_id'] !== null ? (int) $item['entidade_id'] : null)) ?>">
                            <?= e($item['titulo']) ?>
                        </a>
                        <div class="small text-secondary">
                            <?= e(rotulo('TIPOS_NOTIFICACAO', $item['tipo'])) ?> · <?= e(data_br($item['criado_em'], true)) ?>
                        </div>
                    </div>
                    <div class="d-flex gap-1">
                        <?php if ((int) $item['urgente'] === 1) : ?><span class="badge text-bg-danger">Urgente</span><?php endif; ?>
                        <?php if ((int) $item['nova'] === 1) : ?><span class="badge text-bg-success">Nova</span><?php endif; ?>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </section>
<?php endif; ?>
