<?php $usuario = usuario_logado(); ?>
<div class="cabecalho-pagina">
    <div>
        <h1 class="h3 mb-1">Olá, <?= e(explode(' ', (string) $usuario['nome'])[0]) ?></h1>
        <p class="text-secondary mb-0">Acompanhe suas trilhas e os comunicados da CPAT.</p>
    </div>
    <a class="btn btn-primario" href="<?= e(url('/gestor/trilhas')) ?>">Ver todas as trilhas</a>
</div>

<div class="row g-3 mb-4">
    <?php foreach ([
        ['/videos', 'bi-camera-video', 'Vídeos operacionais', 'Passo a passo do AJURI e das rotinas'],
        ['/materiais', 'bi-journal-text', 'Materiais', 'Cartilhas, checklists e modelos'],
        ['/legislacao', 'bi-bank', 'Legislação', 'Bases legais e normativos'],
        ['/faq', 'bi-question-circle', 'Perguntas frequentes', 'Respostas rápidas'],
        ['/contatos', 'bi-telephone', 'Contatos', 'CPAT, DBM e DBI'],
    ] as [$caminho, $icone, $nome, $apoio]) : ?>
        <div class="col-6 col-lg">
            <a class="atalho" href="<?= e(url($caminho)) ?>">
                <i class="bi <?= e($icone) ?>" aria-hidden="true"></i>
                <span class="atalho-nome"><?= e($nome) ?></span>
                <span class="atalho-apoio"><?= e($apoio) ?></span>
            </a>
        </div>
    <?php endforeach; ?>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <section class="painel">
            <h2 class="h5">Minhas trilhas</h2>

            <?php if ($minhasTrilhas === []) : ?>
                <p class="text-secondary mb-0">
                    Você ainda não iniciou nenhuma trilha.
                    <?php if ($trilhasDisponiveis > 0) : ?>
                        Há <?= (int) $trilhasDisponiveis ?> disponível(is) para começar.
                    <?php endif; ?>
                </p>
            <?php else : ?>
                <?php foreach ($minhasTrilhas as $trilha) : ?>
                    <div class="item-lista">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <a class="fw-semibold" href="<?= e(url('/gestor/trilha', ['id' => (int) $trilha['id']])) ?>"><?= e($trilha['titulo']) ?></a>
                            <span class="badge <?= e(classe_andamento($trilha['inscricao_situacao'])) ?>"><?= e(texto_andamento($trilha['inscricao_situacao'])) ?></span>
                        </div>
                        <progress class="progresso mt-2" max="<?= max(1, (int) $trilha['total_modulos']) ?>"
                                  value="<?= (int) $trilha['modulos_concluidos'] ?>"></progress>
                        <div class="text-secondary small">
                            <?= (int) $trilha['modulos_concluidos'] ?> de <?= (int) $trilha['total_modulos'] ?> módulos concluídos
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
    </div>

    <div class="col-lg-4">
        <section class="painel">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h2 class="h5 mb-0">Avisos</h2>
                <a href="<?= e(url('/avisos')) ?>" class="small">Ver todos</a>
            </div>
            <?php if ($avisos === []) : ?>
                <p class="text-secondary mb-0">Nenhum aviso no momento.</p>
            <?php else : ?>
                <?php foreach ($avisos as $aviso) : ?>
                    <div class="item-lista">
                        <strong>
                            <?php if ((int) ($aviso['urgente'] ?? 0) === 1) : ?><i class="bi bi-exclamation-triangle-fill text-danger" aria-hidden="true"></i><?php endif; ?>
                            <?= e($aviso['titulo']) ?>
                        </strong>
                        <div class="text-secondary small mb-1"><?= e(data_br($aviso['publicado_em'])) ?></div>
                        <div><?= e(mb_strimwidth((string) $aviso['mensagem'], 0, 140, '...')) ?></div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
    </div>
</div>
