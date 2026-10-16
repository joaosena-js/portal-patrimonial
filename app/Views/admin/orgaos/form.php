<?php $editando = !empty($orgao); ?>
<?= migalhas([['Órgãos e unidades', '/admin/orgaos'], [$titulo]]) ?>

<div class="coluna-formulario">
    <h1 class="h3 mb-3"><?= e($titulo) ?></h1>

    <form method="post" action="<?= e(url($editando ? '/admin/orgaos/atualizar' : '/admin/orgaos/salvar')) ?>" novalidate>
        <?= csrf_field() ?>
        <?php if ($editando) : ?><input type="hidden" name="id" value="<?= (int) $orgao['id'] ?>"><?php endif; ?>

        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="sigla" class="form-label">Sigla</label>
                <input type="text" class="form-control<?= classe_invalido($erros, 'sigla') ?>" id="sigla" name="sigla" value="<?= e($valores['sigla']) ?>" maxlength="20" required>
                <?= mensagem_erro($erros, 'sigla') ?>
            </div>
            <div class="col-md-8 mb-3">
                <label for="nome" class="form-label">Nome</label>
                <input type="text" class="form-control<?= classe_invalido($erros, 'nome') ?>" id="nome" name="nome" value="<?= e($valores['nome']) ?>" maxlength="190" required>
                <?= mensagem_erro($erros, 'nome') ?>
            </div>
        </div>

        <?php if ($editando) : ?>
            <div class="mb-4">
                <label for="situacao" class="form-label">Situação</label>
                <select class="form-select<?= classe_invalido($erros, 'situacao') ?>" id="situacao" name="situacao">
                    <option value="ativo"<?= $valores['situacao'] === 'ativo' ? ' selected' : '' ?>>Ativo</option>
                    <option value="inativo"<?= $valores['situacao'] === 'inativo' ? ' selected' : '' ?>>Inativo</option>
                </select>
                <div class="form-text">Órgãos inativos deixam de aparecer no autocadastro.</div>
                <?= mensagem_erro($erros, 'situacao') ?>
            </div>
        <?php endif; ?>

        <button type="submit" class="btn btn-primario"><?= $editando ? 'Salvar alterações' : 'Cadastrar' ?></button>
        <a class="btn btn-link" href="<?= e(url('/admin/orgaos')) ?>">Cancelar</a>
    </form>
</div>
