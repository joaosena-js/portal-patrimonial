<?= migalhas([['Usuários']]) ?>

<div class="cabecalho-pagina">
    <h1 class="h3 mb-0">Usuários</h1>
    <a class="btn btn-primario" href="<?= e(url('/admin/usuarios/cadastrar')) ?>">Novo usuário</a>
</div>

<form method="get" action="<?= e(url('/admin/usuarios')) ?>" class="row g-2 mb-3" role="search">
    <div class="col-sm-6 col-md-5">
        <label for="busca" class="visually-hidden">Buscar por nome, usuário ou e-mail</label>
        <input type="search" class="form-control" id="busca" name="busca" value="<?= e($busca) ?>"
               placeholder="Buscar por nome, usuário ou e-mail" maxlength="100">
    </div>
    <div class="col-sm-4 col-md-3">
        <label for="situacao" class="visually-hidden">Situação</label>
        <select class="form-select" id="situacao" name="situacao">
            <option value="">Todas as situações</option>
            <?php foreach (\App\Helpers\Rotulos::SITUACOES_USUARIO as $chave => $texto) : ?>
                <option value="<?= e($chave) ?>"<?= $situacao === $chave ? ' selected' : '' ?>><?= e($texto) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-outline-primario">Filtrar</button>
        <?php if ($busca !== '' || $situacao !== '') : ?>
            <a class="btn btn-link" href="<?= e(url('/admin/usuarios')) ?>">Limpar</a>
        <?php endif; ?>
    </div>
</form>

<section class="painel p-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Perfil</th>
                    <th>Órgão</th>
                    <th>Situação</th>
                    <th>Último acesso</th>
                    <th><span class="visually-hidden">Ações</span></th>
                </tr>
            </thead>
            <tbody>
            <?php if ($usuarios === []) : ?>
                <tr><td colspan="6" class="text-center text-secondary py-4">Nenhum usuário encontrado.</td></tr>
            <?php endif; ?>
            <?php foreach ($usuarios as $item) : ?>
                <?php
                $classeSituacao = match ($item['situacao']) {
                    'ativo' => 'text-bg-success',
                    'pendente' => 'text-bg-warning',
                    'rejeitado' => 'text-bg-danger',
                    default => 'text-bg-secondary',
                };
                ?>
                <tr>
                    <td>
                        <div class="fw-semibold"><?= e($item['nome']) ?></div>
                        <div class="text-secondary small"><?= e($item['usuario']) ?> · <?= e($item['email']) ?></div>
                        <?php if (!empty($item['setor']) || !empty($item['cargo'])) : ?>
                            <div class="text-secondary small"><?= e(trim(($item['cargo'] ?? '') . ' · ' . ($item['setor'] ?? ''), ' ·')) ?></div>
                        <?php endif; ?>
                    </td>
                    <td><?= e(rotulo('PERFIS', $item['perfil'])) ?></td>
                    <td><?= e($item['orgao_sigla'] ?? '-') ?></td>
                    <td><span class="badge <?= e($classeSituacao) ?>"><?= e(rotulo('SITUACOES_USUARIO', $item['situacao'])) ?></span></td>
                    <td><?= $item['ultimo_acesso'] ? e(data_br($item['ultimo_acesso'], true)) : '-' ?></td>
                    <td class="text-end text-nowrap">
                        <?php if ($item['situacao'] === 'pendente') : ?>
                            <form method="post" action="<?= e(url('/admin/usuarios/aprovar')) ?>" class="d-inline">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-success">Aprovar</button>
                            </form>
                            <form method="post" action="<?= e(url('/admin/usuarios/rejeitar')) ?>" class="d-inline">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                        data-confirmar="Rejeitar o cadastro de <?= e($item['nome']) ?>?">Rejeitar</button>
                            </form>
                        <?php endif; ?>
                        <a class="btn btn-sm btn-outline-primario" href="<?= e(url('/admin/usuarios/editar', ['id' => (int) $item['id']])) ?>">Editar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<div class="d-flex justify-content-between align-items-center mt-3">
    <span class="text-secondary small"><?= (int) $total ?> usuário(s)</span>
    <?php include __DIR__ . '/../partials/paginacao.php'; ?>
</div>
