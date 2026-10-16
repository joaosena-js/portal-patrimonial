<nav class="mb-2"><a class="small" href="<?= e(url('/admin/trilhas')) ?>">Voltar às trilhas</a></nav>

<div class="cabecalho-pagina">
    <div>
        <h1 class="h3 mb-1">Módulos da trilha</h1>
        <div class="text-secondary">
            <?= e($trilha['titulo']) ?> ·
            <span class="badge <?= $trilha['situacao'] === 'publicada' ? 'text-bg-success' : 'text-bg-secondary' ?>"><?= e(rotulo('SITUACOES_TRILHA', $trilha['situacao'])) ?></span>
        </div>
    </div>
    <div class="d-flex gap-2">
        <a class="btn btn-outline-primario" href="<?= e(url('/admin/trilhas/editar', ['id' => (int) $trilha['id']])) ?>">Editar trilha</a>
        <a class="btn btn-primario" href="<?= e(url('/admin/modulos/cadastrar', ['trilha_id' => (int) $trilha['id']])) ?>">Novo módulo</a>
    </div>
</div>

<section class="painel p-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th class="text-end">Ordem</th>
                    <th>Módulo</th>
                    <th>Tipo</th>
                    <th>Duração</th>
                    <th>Visibilidade</th>
                    <th><span class="visually-hidden">Ações</span></th>
                </tr>
            </thead>
            <tbody>
            <?php if ($modulos === []) : ?>
                <tr><td colspan="6" class="text-center text-secondary py-4">Nenhum módulo cadastrado nesta trilha.</td></tr>
            <?php endif; ?>
            <?php foreach ($modulos as $modulo) : ?>
                <tr>
                    <td class="text-end"><?= (int) $modulo['ordem'] ?></td>
                    <td class="fw-semibold"><?= e($modulo['titulo']) ?></td>
                    <td><?= e(rotulo('TIPOS_MODULO', $modulo['tipo'])) ?></td>
                    <td><?= !empty($modulo['carga_horaria_min']) ? (int) $modulo['carga_horaria_min'] . ' min' : '-' ?></td>
                    <td>
                        <span class="badge <?= (int) $modulo['ativo'] === 1 ? 'text-bg-success' : 'text-bg-secondary' ?>">
                            <?= (int) $modulo['ativo'] === 1 ? 'Visível' : 'Oculto' ?>
                        </span>
                    </td>
                    <td class="text-end">
                        <a class="btn btn-sm btn-outline-primario" href="<?= e(url('/admin/modulos/editar', ['id' => (int) $modulo['id']])) ?>">Editar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
