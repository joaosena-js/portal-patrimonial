<div class="cabecalho-pagina">
    <h1 class="h3 mb-0">Painel administrativo</h1>
</div>

<?php if ((int) $resumo['gestores_pendentes'] > 0) : ?>
    <div class="alert alert-warning d-flex flex-wrap justify-content-between align-items-center gap-2" role="alert">
        <span><i class="bi bi-person-exclamation" aria-hidden="true"></i>
            <strong><?= (int) $resumo['gestores_pendentes'] ?></strong> cadastro(s) de gestor aguardando aprovação.</span>
        <a class="btn btn-sm btn-primario" href="<?= e(url('/admin/usuarios', ['situacao' => 'pendente'])) ?>">Analisar cadastros</a>
    </div>
<?php endif; ?>

<?php
$indicadores = [
    ['usuarios_cadastrados', 'Usuários cadastrados', 'bi-people'],
    ['usuarios_ativos', 'Usuários ativos', 'bi-person-check'],
    ['orgaos', 'Órgãos cadastrados', 'bi-building'],
    ['videos', 'Vídeos publicados', 'bi-camera-video'],
    ['materiais', 'Materiais publicados', 'bi-journal-text'],
    ['avisos_ativos', 'Avisos ativos', 'bi-megaphone'],
    ['trilhas_publicadas', 'Trilhas publicadas', 'bi-mortarboard'],
    ['inscricoes', 'Trilhas iniciadas', 'bi-play-circle'],
    ['concluidas', 'Trilhas concluídas', 'bi-check-circle'],
];
?>
<div class="row g-3 mb-4">
    <?php foreach ($indicadores as [$chave, $nome, $icone]) : ?>
        <div class="col-6 col-lg-4">
            <div class="indicador">
                <div class="indicador-valor"><?= (int) $resumo[$chave] ?></div>
                <div class="indicador-nome"><i class="bi <?= e($icone) ?>" aria-hidden="true"></i> <?= e($nome) ?></div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <section class="painel">
            <h2 class="h5">Acompanhamento por trilha</h2>
            <?php if ($acompanhamento === []) : ?>
                <p class="text-secondary mb-0">Nenhuma trilha publicada até o momento.</p>
            <?php else : ?>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Trilha</th>
                                <th class="text-end">Iniciaram</th>
                                <th class="text-end">Concluíram</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($acompanhamento as $linha) : ?>
                                <tr>
                                    <td><?= e($linha['titulo']) ?></td>
                                    <td class="text-end"><?= (int) $linha['inscritos'] ?></td>
                                    <td class="text-end"><?= (int) $linha['concluidos'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </div>

    <div class="col-lg-5">
        <section class="painel mb-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h2 class="h5 mb-0">Avisos em vigor</h2>
                <a class="btn btn-sm btn-outline-primario" href="<?= e(url('/admin/avisos/cadastrar')) ?>">Novo aviso</a>
            </div>
            <?php if ($avisos === []) : ?>
                <p class="text-secondary mb-0">Nenhum aviso em vigor.</p>
            <?php else : ?>
                <ul class="list-unstyled mb-0">
                    <?php foreach ($avisos as $aviso) : ?>
                        <li class="item-lista">
                            <strong><?= e($aviso['titulo']) ?></strong>
                            <div class="text-secondary small">Publicado em <?= e(data_br($aviso['publicado_em'])) ?></div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>

        <section class="painel">
            <h2 class="h5">Atividades recentes</h2>
            <?php if ($atividades === []) : ?>
                <p class="text-secondary mb-0">Sem registros ainda.</p>
            <?php else : ?>
                <ul class="list-unstyled mb-0">
                    <?php foreach ($atividades as $atividade) : ?>
                        <li class="item-lista">
                            <div><?= e($atividade['descricao'] ?? $atividade['acao']) ?></div>
                            <div class="text-secondary small">
                                <?= e($atividade['usuario'] ?? 'Sistema') ?> em <?= e(data_br($atividade['criado_em'], true)) ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>
    </div>
</div>