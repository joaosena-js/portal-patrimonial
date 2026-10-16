<?php
$editando = !empty($pergunta);
$rotulosVinculo = [
    'video' => ['vinculo_video', 'Vídeos'],
    'material' => ['vinculo_material', 'Materiais'],
    'legislacao' => ['vinculo_legislacao', 'Legislação'],
    'contato' => ['vinculo_contato', 'Contatos'],
];
?>
<?= migalhas([['Perguntas frequentes', '/admin/faq'], [$titulo]]) ?>

<div class="coluna-formulario">
    <h1 class="h3 mb-3"><?= e($titulo) ?></h1>

    <form method="post" action="<?= e(url($editando ? '/admin/faq/atualizar' : '/admin/faq/salvar')) ?>" novalidate>
        <?= csrf_field() ?>
        <?php if ($editando) : ?><input type="hidden" name="id" value="<?= (int) $pergunta['id'] ?>"><?php endif; ?>

        <div class="row">
            <div class="col-md-8 mb-3">
                <label for="categoria" class="form-label">Categoria</label>
                <select class="form-select<?= classe_invalido($erros, 'categoria') ?>" id="categoria" name="categoria">
                    <?php foreach (\App\Helpers\Rotulos::CATEGORIAS as $chave => $texto) : ?>
                        <option value="<?= e($chave) ?>"<?= $valores['categoria'] === $chave ? ' selected' : '' ?>><?= e($texto) ?></option>
                    <?php endforeach; ?>
                </select>
                <?= mensagem_erro($erros, 'categoria') ?>
            </div>
            <div class="col-md-4 mb-3">
                <label for="ordem" class="form-label">Ordem na categoria</label>
                <input type="number" class="form-control<?= classe_invalido($erros, 'ordem') ?>" id="ordem" name="ordem"
                       value="<?= e($valores['ordem']) ?>" min="1" max="9999" placeholder="Automática">
                <?= mensagem_erro($erros, 'ordem') ?>
            </div>
        </div>

        <div class="mb-3">
            <label for="pergunta" class="form-label">Pergunta</label>
            <input type="text" class="form-control<?= classe_invalido($erros, 'pergunta') ?>" id="pergunta" name="pergunta"
                   value="<?= e($valores['pergunta']) ?>" maxlength="255" required>
            <?= mensagem_erro($erros, 'pergunta') ?>
        </div>

        <div class="mb-4">
            <label for="resposta" class="form-label">Resposta</label>
            <textarea class="form-control<?= classe_invalido($erros, 'resposta') ?>" id="resposta" name="resposta"
                      rows="8" maxlength="5000" required><?= e($valores['resposta']) ?></textarea>
            <div class="form-text">Texto simples. Quebras de linha são preservadas.</div>
            <?= mensagem_erro($erros, 'resposta') ?>
        </div>

        <fieldset class="mb-4">
            <legend class="h6">Conteúdos relacionados (opcional)</legend>
            <p class="small text-secondary">Segure Ctrl (ou Cmd) para marcar mais de um item em cada lista.</p>
            <div class="row">
                <?php foreach ($rotulosVinculo as $tipo => [$campo, $rotuloLista]) : ?>
                    <div class="col-md-6 mb-3">
                        <label for="<?= e($campo) ?>" class="form-label"><?= e($rotuloLista) ?></label>
                        <select class="form-select" id="<?= e($campo) ?>" name="<?= e($campo) ?>[]" multiple size="5">
                            <?php foreach ($opcoes[$tipo] as $id => $texto) : ?>
                                <option value="<?= (int) $id ?>"<?= in_array((int) $id, $vinculos[$tipo] ?? [], true) ? ' selected' : '' ?>><?= e($texto) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endforeach; ?>
            </div>
        </fieldset>

        <div class="form-check mb-4">
            <input class="form-check-input" type="checkbox" id="ativo" name="ativo" value="1"<?= $valores['ativo'] === '1' ? ' checked' : '' ?>>
            <label class="form-check-label" for="ativo">Visível para os gestores</label>
        </div>

        <button type="submit" class="btn btn-primario"><?= $editando ? 'Salvar alterações' : 'Cadastrar pergunta' ?></button>
        <a class="btn btn-link" href="<?= e(url('/admin/faq')) ?>">Cancelar</a>
    </form>
</div>
