<?php $editando = !empty($grupo); ?>
<?= migalhas([['Materiais de apoio', '/admin/materiais'], ['Assuntos', '/admin/materiais/grupos'], [$titulo]]) ?>

<div class="coluna-formulario">
    <h1 class="h3 mb-3"><?= e($titulo) ?></h1>

    <form method="post" action="<?= e(url($editando ? '/admin/materiais/grupos/atualizar' : '/admin/materiais/grupos/salvar')) ?>" novalidate>
        <?= csrf_field() ?>
        <?php if ($editando) : ?><input type="hidden" name="id" value="<?= (int) $grupo['id'] ?>"><?php endif; ?>

        <div class="mb-3">
            <label for="titulo" class="form-label">Título do assunto</label>
            <input type="text" class="form-control<?= classe_invalido($erros, 'titulo') ?>" id="titulo" name="titulo" value="<?= e($valores['titulo']) ?>" maxlength="150" required>
            <?= mensagem_erro($erros, 'titulo') ?>
        </div>

        <div class="mb-3">
            <label for="descricao" class="form-label">Descrição (opcional)</label>
            <textarea class="form-control<?= classe_invalido($erros, 'descricao') ?>" id="descricao" name="descricao" rows="2" maxlength="500"><?= e($valores['descricao']) ?></textarea>
            <?= mensagem_erro($erros, 'descricao') ?>
        </div>

        <div class="mb-3">
            <label for="ordem" class="form-label">Ordem de exibição</label>
            <input type="number" class="form-control<?= classe_invalido($erros, 'ordem') ?>" id="ordem" name="ordem" value="<?= e($valores['ordem']) ?>" min="1" max="9999" required>
            <?= mensagem_erro($erros, 'ordem') ?>
        </div>

        <div class="form-check mb-4">
            <input class="form-check-input" type="checkbox" id="ativo" name="ativo" value="1"<?= $valores['ativo'] === '1' ? ' checked' : '' ?>>
            <label class="form-check-label" for="ativo">Visível para os gestores</label>
        </div>

        <button type="submit" class="btn btn-primario"><?= $editando ? 'Salvar alterações' : 'Cadastrar assunto' ?></button>
        <a class="btn btn-link" href="<?= e(url('/admin/materiais/grupos')) ?>">Cancelar</a>
    </form>
</div>
