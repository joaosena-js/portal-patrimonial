<?php $editando = !empty($documento); ?>
<?= migalhas([['Legislação e normativos', '/admin/legislacao'], [$titulo]]) ?>

<div class="coluna-formulario">
    <h1 class="h3 mb-3"><?= e($titulo) ?></h1>

    <form method="post" action="<?= e(url($editando ? '/admin/legislacao/atualizar' : '/admin/legislacao/salvar')) ?>" enctype="multipart/form-data" novalidate>
        <?= csrf_field() ?>
        <?php if ($editando) : ?><input type="hidden" name="id" value="<?= (int) $documento['id'] ?>"><?php endif; ?>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="categoria" class="form-label">Categoria</label>
                <select class="form-select<?= classe_invalido($erros, 'categoria') ?>" id="categoria" name="categoria">
                    <?php foreach (\App\Helpers\Rotulos::CATEGORIAS_LEGISLACAO as $chave => $texto) : ?>
                        <option value="<?= e($chave) ?>"<?= $valores['categoria'] === $chave ? ' selected' : '' ?>><?= e($texto) ?></option>
                    <?php endforeach; ?>
                </select>
                <?= mensagem_erro($erros, 'categoria') ?>
            </div>
            <div class="col-md-6 mb-3">
                <label for="numero" class="form-label">Número (opcional)</label>
                <input type="text" class="form-control<?= classe_invalido($erros, 'numero') ?>" id="numero" name="numero" value="<?= e($valores['numero']) ?>" maxlength="60" placeholder="Ex.: Decreto nº 0000/0000">
                <?= mensagem_erro($erros, 'numero') ?>
            </div>
        </div>

        <div class="mb-3">
            <label for="titulo" class="form-label">Título</label>
            <input type="text" class="form-control<?= classe_invalido($erros, 'titulo') ?>" id="titulo" name="titulo" value="<?= e($valores['titulo']) ?>" maxlength="190" required>
            <?= mensagem_erro($erros, 'titulo') ?>
        </div>

        <div class="mb-3">
            <label for="ementa" class="form-label">Do que trata (resumo)</label>
            <textarea class="form-control<?= classe_invalido($erros, 'ementa') ?>" id="ementa" name="ementa" rows="3" maxlength="1000"><?= e($valores['ementa']) ?></textarea>
            <div class="form-text">Explique em poucas linhas o assunto do documento. Esse texto também entra na pesquisa.</div>
            <?= mensagem_erro($erros, 'ementa') ?>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="data_publicacao" class="form-label">Data de publicação (opcional)</label>
                <input type="date" class="form-control<?= classe_invalido($erros, 'data_publicacao') ?>" id="data_publicacao" name="data_publicacao" value="<?= e($valores['data_publicacao']) ?>">
                <?= mensagem_erro($erros, 'data_publicacao') ?>
            </div>
            <div class="col-md-6 mb-3">
                <label for="situacao" class="form-label">Situação</label>
                <select class="form-select<?= classe_invalido($erros, 'situacao') ?>" id="situacao" name="situacao">
                    <?php foreach (\App\Helpers\Rotulos::SITUACOES_LEGISLACAO as $chave => $texto) : ?>
                        <option value="<?= e($chave) ?>"<?= $valores['situacao'] === $chave ? ' selected' : '' ?>><?= e($texto) ?></option>
                    <?php endforeach; ?>
                </select>
                <?= mensagem_erro($erros, 'situacao') ?>
            </div>
        </div>

        <div class="mb-3">
            <label for="arquivo" class="form-label">Arquivo (PDF, DOCX ou XLSX)</label>
            <input type="file" class="form-control<?= classe_invalido($erros, 'arquivo') ?>" id="arquivo" name="arquivo" accept=".pdf,.docx,.xlsx">
            <div class="form-text">
                Limite do portal: <?= (int) $limiteMb ?> MB. Envie o arquivo, informe o link abaixo, ou ambos.
                <?php if ($editando && $documento['arquivo_id'] !== null) : ?>
                    Já existe um arquivo enviado. Selecione outro apenas para substituí-lo.
                <?php endif; ?>
            </div>
            <?= mensagem_erro($erros, 'arquivo') ?>
        </div>

        <div class="mb-3">
            <label for="url" class="form-label">Link para consulta (opcional)</label>
            <input type="url" class="form-control<?= classe_invalido($erros, 'url') ?>" id="url" name="url" value="<?= e($valores['url']) ?>" maxlength="500" placeholder="https://">
            <?= mensagem_erro($erros, 'url') ?>
        </div>

        <div class="form-check mb-4">
            <input class="form-check-input" type="checkbox" id="ativo" name="ativo" value="1"<?= $valores['ativo'] === '1' ? ' checked' : '' ?>>
            <label class="form-check-label" for="ativo">Visível para os gestores</label>
        </div>

        <button type="submit" class="btn btn-primario"><?= $editando ? 'Salvar alterações' : 'Cadastrar documento' ?></button>
        <a class="btn btn-link" href="<?= e(url('/admin/legislacao')) ?>">Cancelar</a>
    </form>
</div>
