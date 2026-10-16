<?php
/** Campos compartilhados entre cadastro e edição de trilha. Variáveis: $valores, $erros, $trilha (opcional). */
$editando = !empty($trilha);
?>
<div class="mb-3">
    <label for="titulo" class="form-label">Título da trilha</label>
    <input type="text" class="form-control<?= classe_invalido($erros, 'titulo') ?>" id="titulo" name="titulo"
           value="<?= e($valores['titulo']) ?>" maxlength="150" required>
    <?= mensagem_erro($erros, 'titulo') ?>
</div>

<div class="mb-3">
    <label for="descricao" class="form-label">Descrição</label>
    <textarea class="form-control<?= classe_invalido($erros, 'descricao') ?>" id="descricao" name="descricao"
              rows="4" maxlength="1000"><?= e($valores['descricao']) ?></textarea>
    <div class="form-text">Explique para quem a trilha é indicada e o que será abordado.</div>
    <?= mensagem_erro($erros, 'descricao') ?>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="area" class="form-label">Área</label>
        <select class="form-select<?= classe_invalido($erros, 'area') ?>" id="area" name="area">
            <?php foreach (\App\Helpers\Rotulos::AREAS as $chave => $texto) : ?>
                <option value="<?= e($chave) ?>"<?= $valores['area'] === $chave ? ' selected' : '' ?>><?= e($texto) ?></option>
            <?php endforeach; ?>
        </select>
        <?= mensagem_erro($erros, 'area') ?>
    </div>

    <?php if ($editando) : ?>
        <div class="col-md-6 mb-3">
            <label for="situacao" class="form-label">Situação</label>
            <select class="form-select<?= classe_invalido($erros, 'situacao') ?>" id="situacao" name="situacao">
                <?php foreach (\App\Helpers\Rotulos::SITUACOES_TRILHA as $chave => $texto) : ?>
                    <option value="<?= e($chave) ?>"<?= $valores['situacao'] === $chave ? ' selected' : '' ?>><?= e($texto) ?></option>
                <?php endforeach; ?>
            </select>
            <div class="form-text">Somente trilhas publicadas aparecem para os gestores.</div>
            <?= mensagem_erro($erros, 'situacao') ?>
        </div>
    <?php endif; ?>
</div>
