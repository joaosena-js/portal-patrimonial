<div class="cabecalho-pagina">
    <h1 class="h3 mb-0">Trilhas de capacitação</h1>
    <a class="btn btn-primario" href="<?= e(url('/admin/trilhas/cadastrar')) ?>">Nova trilha</a>
</div>

<section class="painel p-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Trilha</th>
                    <th>Área</th>
                    <th>Situação</th>
                    <th class="text-end">Módulos ativos</th>
                    <th>Atualizada em</th>
                    <th><span class="visually-hidden">Ações</span></th>
                </tr>
            </thead>
            <tbody>
            <?php if ($trilhas === []) : ?>
                <tr><td colspan="6" class="text-center text-secondary py-4">Nenhuma trilha cadastrada.</td></tr>
            <?php endif; ?>
            <?php foreach ($trilhas as $trilha) : ?>
                <tr>
                    <td class="fw-semibold"><?= e($trilha['titulo']) ?></td>
                    <td><?= e(rotulo('AREAS', $trilha['area'])) ?></td>
                    <td>
                        <span class="badge <?= $trilha['situacao'] === 'publicada' ? 'text-bg-success' : 'text-bg-secondary' ?>">
                            <?= e(rotulo('SITUACOES_TRILHA', $trilha['situacao'])) ?>
                        </span>
                    </td>
                    <td class="text-end"><?= (int) $trilha['total_modulos'] ?></td>
                    <td><?= e(data_br($trilha['atualizado_em'])) ?></td>
                    <td class="text-end text-nowrap">
                        <a class="btn btn-sm btn-outline-primario" href="<?= e(url('/admin/modulos', ['trilha_id' => (int) $trilha['id']])) ?>">Módulos</a>
                        <a class="btn btn-sm btn-outline-primario" href="<?= e(url('/admin/trilhas/editar', ['id' => (int) $trilha['id']])) ?>">Editar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
