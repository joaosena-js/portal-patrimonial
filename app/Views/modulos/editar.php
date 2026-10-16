<div class="coluna-formulario">
    <nav class="mb-2"><a class="small" href="<?= e(url('/admin/modulos', ['trilha_id' => (int) $trilha['id']])) ?>">Voltar aos módulos</a></nav>
    <h1 class="h3 mb-1">Editar módulo</h1>
    <p class="text-secondary mb-3">Trilha: <?= e($trilha['titulo']) ?></p>

    <form method="post" action="<?= e(url('/admin/modulos/atualizar')) ?>" novalidate>
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= (int) $modulo['id'] ?>">
        <?php include __DIR__ . '/_formulario.php'; ?>

        <button type="submit" class="btn btn-primario">Salvar alterações</button>
        <a class="btn btn-link" href="<?= e(url('/admin/modulos', ['trilha_id' => (int) $trilha['id']])) ?>">Cancelar</a>
    </form>
</div>
