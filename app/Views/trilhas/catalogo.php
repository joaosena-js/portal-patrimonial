<div class="cabecalho-pagina">
    <h1 class="h3 mb-0">Trilhas de capacitação</h1>
</div>

<?php if ($trilhas === []) : ?>
    <p class="text-secondary">Nenhuma trilha publicada no momento.</p>
<?php endif; ?>

<div class="row g-3">
    <?php foreach ($trilhas as $trilha) : ?>
        <div class="col-md-6">
            <article class="painel h-100 d-flex flex-column">
                <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                    <h2 class="h5 mb-0"><?= e($trilha['titulo']) ?></h2>
                    <span class="badge <?= e(classe_andamento($trilha['inscricao_situacao'])) ?>"><?= e(texto_andamento($trilha['inscricao_situacao'])) ?></span>
                </div>
                <div class="text-secondary small mb-2">
                    <?= e(rotulo('AREAS', $trilha['area'])) ?> · <?= (int) $trilha['total_modulos'] ?> módulo(s)
                </div>
                <?php if (!empty($trilha['descricao'])) : ?>
                    <p class="mb-3"><?= e($trilha['descricao']) ?></p>
                <?php endif; ?>

                <div class="mt-auto">
                    <?php if ($trilha['inscricao_situacao'] !== null) : ?>
                        <progress class="progresso mb-1" max="<?= max(1, (int) $trilha['total_modulos']) ?>"
                                  value="<?= (int) $trilha['modulos_concluidos'] ?>"></progress>
                        <div class="text-secondary small mb-2"><?= (int) $trilha['modulos_concluidos'] ?> de <?= (int) $trilha['total_modulos'] ?> concluídos</div>
                    <?php endif; ?>
                    <a class="btn btn-outline-primario btn-sm" href="<?= e(url('/gestor/trilha', ['id' => (int) $trilha['id']])) ?>">
                        <?= $trilha['inscricao_situacao'] === null ? 'Ver detalhes' : 'Continuar' ?>
                    </a>
                </div>
            </article>
        </div>
    <?php endforeach; ?>
</div>
