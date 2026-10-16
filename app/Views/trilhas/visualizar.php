<nav aria-label="Navegação" class="mb-3">
    <a href="<?= e(url('/gestor/trilhas')) ?>" class="small">Voltar às trilhas</a>
</nav>

<div class="cabecalho-pagina">
    <div>
        <h1 class="h3 mb-1"><?= e($trilha['titulo']) ?></h1>
        <div class="text-secondary">
            <?= e(rotulo('AREAS', $trilha['area'])) ?> · <?= count($modulos) ?> módulo(s)
            <?php if ($inscricao !== null) : ?>
                · <span class="badge <?= e(classe_andamento($inscricao['situacao'])) ?>"><?= e(texto_andamento($inscricao['situacao'])) ?></span>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($inscricao === null) : ?>
        <form method="post" action="<?= e(url('/gestor/trilha/inscrever')) ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="trilha_id" value="<?= (int) $trilha['id'] ?>">
            <button type="submit" class="btn btn-primario">Iniciar trilha</button>
        </form>
    <?php endif; ?>
</div>

<?php if (!empty($trilha['descricao'])) : ?>
    <p class="texto-leitura"><?= e($trilha['descricao']) ?></p>
<?php endif; ?>

<section class="painel p-0">
    <?php if ($modulos === []) : ?>
        <p class="text-secondary m-3">Esta trilha ainda não possui módulos disponíveis.</p>
    <?php endif; ?>

    <ol class="lista-modulos">
        <?php foreach ($modulos as $modulo) : ?>
            <?php $feito = in_array((int) $modulo['id'], $concluidos, true); ?>
            <li class="modulo-linha">
                <div>
                    <div class="fw-semibold"><?= e($modulo['titulo']) ?></div>
                    <div class="text-secondary small">
                        <?= e(rotulo('TIPOS_MODULO', $modulo['tipo'])) ?>
                        <?php if (!empty($modulo['carga_horaria_min'])) : ?>
                            · <?= (int) $modulo['carga_horaria_min'] ?> min
                        <?php endif; ?>
                    </div>
                </div>
                <div class="text-nowrap">
                    <?php if ($feito) : ?>
                        <span class="badge text-bg-success me-2">Concluído</span>
                    <?php endif; ?>
                    <?php if ($inscricao !== null) : ?>
                        <a class="btn btn-sm btn-outline-primario" href="<?= e(url('/gestor/modulo', ['id' => (int) $modulo['id']])) ?>">
                            <?= $feito ? 'Revisar' : 'Abrir' ?>
                        </a>
                    <?php endif; ?>
                </div>
            </li>
        <?php endforeach; ?>
    </ol>
</section>

<?php if ($inscricao === null && $modulos !== []) : ?>
    <p class="text-secondary small mt-3">Inicie a trilha para abrir os módulos e registrar seu progresso.</p>
<?php endif; ?>
