<div class="coluna-formulario">
    <h1 class="h3 mb-3">Novo usuário</h1>

    <form method="post" action="<?= e(url('/admin/usuarios/salvar')) ?>" novalidate>
        <?= csrf_field() ?>
        <?php include __DIR__ . '/_formulario.php'; ?>

        <button type="submit" class="btn btn-primario">Cadastrar</button>
        <a class="btn btn-link" href="<?= e(url('/admin/usuarios')) ?>">Cancelar</a>
    </form>
</div>
