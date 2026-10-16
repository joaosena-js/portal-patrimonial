<div class="coluna-formulario">
    <h1 class="h3 mb-3">Nova trilha</h1>

    <form method="post" action="<?= e(url('/admin/trilhas/salvar')) ?>" novalidate>
        <?= csrf_field() ?>
        <?php include __DIR__ . '/_formulario.php'; ?>

        <button type="submit" class="btn btn-primario">Criar trilha</button>
        <a class="btn btn-link" href="<?= e(url('/admin/trilhas')) ?>">Cancelar</a>
    </form>
</div>
