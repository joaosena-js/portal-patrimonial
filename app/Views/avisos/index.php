<div class="cabecalho-pagina">
    <h1 class="h3 mb-0">Quadro de avisos</h1>
    <?php if ($ehAdmin) : ?>
        <a class="btn btn-primario" href="<?= e(url('/admin/avisos/cadastrar')) ?>">Novo aviso</a>
    <?php endif; ?>
</div>

<?php if ($avisos === []) : ?>
    <p class="text-secondary">Nenhum aviso publicado.</p>
<?php endif; ?>

<?php foreach ($avisos as $aviso) : ?>
    <?php $visivel = !$ehAdmin || (int) $aviso['visivel'] === 1; ?>
    <article class="painel mb-3<?= (int) ($aviso['urgente'] ?? 0) === 1 ? ' aviso-urgente' : '' ?>">
        <div class="d-flex justify-content-between align-items-start gap-2">
            <h2 class="h5 mb-1">
                <?php if ((int) ($aviso['urgente'] ?? 0) === 1) : ?><i class="bi bi-exclamation-triangle-fill text-danger" aria-hidden="true"></i><?php endif; ?>
                <?= e($aviso['titulo']) ?>
            </h2>
            <?php if ($ehAdmin && !$visivel) : ?>
                <span class="badge text-bg-secondary"><?= (int) $aviso['ativo'] === 1 ? 'Vencido' : 'Retirado' ?></span>
            <?php endif; ?>
        </div>
        <div class="text-secondary small mb-2">
            Publicado em <?= e(data_br($aviso['publicado_em'])) ?>
            <?php if (!empty($aviso['autor'])) : ?> por <?= e($aviso['autor']) ?><?php endif; ?>
            <?php if (!empty($aviso['expira_em'])) : ?> · válido até <?= e(data_br($aviso['expira_em'])) ?><?php endif; ?>
        </div>
        <div class="texto-leitura"><?= nl2br(e($aviso['mensagem'])) ?></div>

        <?php if ($ehAdmin && (int) $aviso['ativo'] === 1) : ?>
            <form method="post" action="<?= e(url('/admin/avisos/desativar')) ?>" class="mt-3">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int) $aviso['id'] ?>">
                <button type="submit" class="btn btn-sm btn-outline-danger"
                        data-confirmar="Retirar este aviso do quadro?">Retirar do quadro</button>
            </form>
        <?php endif; ?>
    </article>
<?php endforeach; ?>
