<?php $editando = !empty($material); ?>
<?= migalhas([['Materiais de apoio', '/admin/materiais'], [$titulo]]) ?>

<div class="coluna-formulario">
    <h1 class="h3 mb-3"><?= e($titulo) ?></h1>

    <?php if ($grupos === []) : ?>
        <div class="alert alert-warning">
            Nenhum assunto cadastrado. <a href="<?= e(url('/admin/materiais/grupos/cadastrar')) ?>">Crie um assunto</a> antes de cadastrar materiais.
        </div>
    <?php endif; ?>

    <form method="post" action="<?= e(url($editando ? '/admin/materiais/atualizar' : '/admin/materiais/salvar')) ?>" enctype="multipart/form-data" novalidate>
        <?= csrf_field() ?>
        <?php if ($editando) : ?><input type="hidden" name="id" value="<?= (int) $material['id'] ?>"><?php endif; ?>

        <div class="mb-3">
            <label for="grupo_id" class="form-label">Assunto (grupo do accordion)</label>
            <select class="form-select<?= classe_invalido($erros, 'grupo_id') ?>" id="grupo_id" name="grupo_id" required>
                <option value="">Selecione</option>
                <?php foreach ($grupos as $grupo) : ?>
                    <option value="<?= (int) $grupo['id'] ?>"<?= (string) $valores['grupo_id'] === (string) $grupo['id'] ? ' selected' : '' ?>>
                        <?= e($grupo['titulo']) ?><?= (int) $grupo['ativo'] === 0 ? ' (oculto)' : '' ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?= mensagem_erro($erros, 'grupo_id') ?>
        </div>

        <div class="mb-3">
            <label for="titulo" class="form-label">Título</label>
            <input type="text" class="form-control<?= classe_invalido($erros, 'titulo') ?>" id="titulo" name="titulo" value="<?= e($valores['titulo']) ?>" maxlength="150" required>
            <?= mensagem_erro($erros, 'titulo') ?>
        </div>

        <div class="mb-3">
            <label for="descricao" class="form-label">Descrição (opcional)</label>
            <textarea class="form-control<?= classe_invalido($erros, 'descricao') ?>" id="descricao" name="descricao" rows="2" maxlength="500"><?= e($valores['descricao']) ?></textarea>
            <?= mensagem_erro($erros, 'descricao') ?>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="tipo" class="form-label">Tipo</label>
                <select class="form-select<?= classe_invalido($erros, 'tipo') ?>" id="tipo" name="tipo">
                    <?php foreach (\App\Helpers\Rotulos::TIPOS_MATERIAL as $chave => $texto) : ?>
                        <option value="<?= e($chave) ?>"<?= $valores['tipo'] === $chave ? ' selected' : '' ?>><?= e($texto) ?></option>
                    <?php endforeach; ?>
                </select>
                <?= mensagem_erro($erros, 'tipo') ?>
            </div>
            <div class="col-md-6 mb-3">
                <label for="publicado_em" class="form-label">Data de publicação</label>
                <input type="date" class="form-control<?= classe_invalido($erros, 'publicado_em') ?>" id="publicado_em" name="publicado_em" value="<?= e($valores['publicado_em']) ?>" required>
                <?= mensagem_erro($erros, 'publicado_em') ?>
            </div>
        </div>

        <div class="mb-3">
            <label for="arquivo" class="form-label">Arquivo (PDF, DOCX ou XLSX)</label>
            <input type="file" class="form-control<?= classe_invalido($erros, 'arquivo') ?>" id="arquivo" name="arquivo" accept=".pdf,.docx,.xlsx">
            <div class="form-text">
                Limite do portal: <?= (int) $limiteMb ?> MB.
                <?php if ($editando) : ?>
                    Arquivo atual: <strong><?= e($material['arquivo_nome']) ?></strong>. Selecione outro apenas para substituí-lo.
                <?php endif; ?>
            </div>
            <?= mensagem_erro($erros, 'arquivo') ?>
        </div>

        <div class="form-check mb-4">
            <input class="form-check-input" type="checkbox" id="ativo" name="ativo" value="1"<?= $valores['ativo'] === '1' ? ' checked' : '' ?>>
            <label class="form-check-label" for="ativo">Visível para os gestores</label>
        </div>

        <button type="submit" class="btn btn-primario"><?= $editando ? 'Salvar alterações' : 'Publicar material' ?></button>
        <a class="btn btn-link" href="<?= e(url('/admin/materiais')) ?>">Cancelar</a>
    </form>
</div>
