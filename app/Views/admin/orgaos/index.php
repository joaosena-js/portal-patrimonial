<?= migalhas([['Órgãos e unidades']]) ?>

<div class="cabecalho-pagina">
    <h1 class="h3 mb-0">Órgãos e unidades</h1>
    <a class="btn btn-primario" href="<?= e(url('/admin/orgaos/cadastrar')) ?>">Novo órgão</a>
</div>

<section class="painel p-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>Sigla</th><th>Nome</th><th>Situação</th><th class="text-end">Usuários</th><th><span class="visually-hidden">Ações</span></th></tr></thead>
            <tbody>
            <?php if ($orgaos === []) : ?>
                <tr><td colspan="5" class="text-center text-secondary py-4">Nenhum órgão cadastrado. Cadastre os órgãos para liberar o autocadastro dos gestores.</td></tr>
            <?php endif; ?>
            <?php foreach ($orgaos as $orgao) : ?>
                <tr>
                    <td class="fw-semibold"><?= e($orgao['sigla']) ?></td>
                    <td><?= e($orgao['nome']) ?></td>
                    <td><span class="badge <?= $orgao['situacao'] === 'ativo' ? 'text-bg-success' : 'text-bg-secondary' ?>"><?= e(rotulo('SITUACOES_USUARIO', $orgao['situacao'])) ?></span></td>
                    <td class="text-end"><?= (int) $orgao['total_usuarios'] ?></td>
                    <td class="text-end"><a class="btn btn-sm btn-outline-primario" href="<?= e(url('/admin/orgaos/editar', ['id' => (int) $orgao['id']])) ?>">Editar</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
