<div class="coluna-formulario">
    <h1 class="h3 mb-3">Editar trilha</h1>

    <form method="post" action="<?= e(url('/admin/trilhas/atualizar')) ?>" novalidate>
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= (int) $trilha['id'] ?>">
        <?php include __DIR__ . '/_formulario.php'; ?>

        <button type="submit" class="btn btn-primario">Salvar alterações</button>
        <a class="btn btn-link" href="<?= e(url('/admin/modulos', ['trilha_id' => (int) $trilha['id']])) ?>">Ver módulos</a>
        <a class="btn btn-link" href="<?= e(url('/admin/trilhas')) ?>">Cancelar</a>
    </form>
</div>
