<div class="coluna-formulario">
    <h1 class="h3 mb-1">Editar usuário</h1>
    <p class="text-secondary small mb-3">
        Usuário de acesso: <strong><?= e($usuario['usuario']) ?></strong> ·
        Último acesso: <?= $usuario['ultimo_acesso'] ? e(data_br($usuario['ultimo_acesso'], true)) : 'nunca acessou' ?>
    </p>

    <form method="post" action="<?= e(url('/admin/usuarios/atualizar')) ?>" novalidate>
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= (int) $usuario['id'] ?>">
        <?php include __DIR__ . '/_formulario.php'; ?>

        <button type="submit" class="btn btn-primario">Salvar alterações</button>
        <a class="btn btn-link" href="<?= e(url('/admin/usuarios')) ?>">Cancelar</a>
    </form>
</div>
