<?php
/** Campos compartilhados entre cadastro e edição de módulo. Variáveis: $valores, $erros, $trilha. */
$ehTexto = $valores['tipo'] === 'texto';
?>
<div class="mb-3">
    <label for="titulo" class="form-label">Título do módulo</label>
    <input type="text" class="form-control<?= classe_invalido($erros, 'titulo') ?>" id="titulo" name="titulo"
           value="<?= e($valores['titulo']) ?>" maxlength="150" required>
    <?= mensagem_erro($erros, 'titulo') ?>
</div>

<div class="mb-3">
    <label for="descricao" class="form-label">Resumo (opcional)</label>
    <textarea class="form-control<?= classe_invalido($erros, 'descricao') ?>" id="descricao" name="descricao"
              rows="2" maxlength="500"><?= e($valores['descricao']) ?></textarea>
    <?= mensagem_erro($erros, 'descricao') ?>
</div>

<div class="mb-3">
    <label for="tipo" class="form-label">Tipo de conteúdo</label>
    <select class="form-select<?= classe_invalido($erros, 'tipo') ?>" id="tipo" name="tipo" data-alternar-tipo>
        <?php foreach (\App\Helpers\Rotulos::TIPOS_MODULO as $chave => $texto) : ?>
            <option value="<?= e($chave) ?>"<?= $valores['tipo'] === $chave ? ' selected' : '' ?>><?= e($texto) ?></option>
        <?php endforeach; ?>
    </select>
    <?= mensagem_erro($erros, 'tipo') ?>
</div>

<div class="mb-3<?= $ehTexto ? '' : ' d-none' ?>" data-campo-tipo="texto">
    <label for="conteudo_texto" class="form-label">Conteúdo</label>
    <textarea class="form-control<?= classe_invalido($erros, 'conteudo_texto') ?>" id="conteudo_texto"
              name="conteudo_texto" rows="10" maxlength="20000"><?= e($valores['conteudo_texto']) ?></textarea>
    <div class="form-text">Texto simples. Quebras de linha são preservadas.</div>
    <?= mensagem_erro($erros, 'conteudo_texto') ?>
</div>

<div class="mb-3<?= $ehTexto ? ' d-none' : '' ?>" data-campo-tipo="url">
    <label for="conteudo_url" class="form-label">Endereço do conteúdo</label>
    <input type="url" class="form-control<?= classe_invalido($erros, 'conteudo_url') ?>" id="conteudo_url"
           name="conteudo_url" value="<?= e($valores['conteudo_url']) ?>" maxlength="500" placeholder="https://">
    <div class="form-text">O conteúdo abre em nova aba. Use endereços http:// ou https://.</div>
    <?= mensagem_erro($erros, 'conteudo_url') ?>
</div>

<div class="row">
    <div class="col-sm-6 mb-3">
        <label for="ordem" class="form-label">Ordem na trilha</label>
        <input type="number" class="form-control<?= classe_invalido($erros, 'ordem') ?>" id="ordem" name="ordem"
               value="<?= e($valores['ordem']) ?>" min="1" max="9999">
        <?= mensagem_erro($erros, 'ordem') ?>
    </div>
    <div class="col-sm-6 mb-3">
        <label for="carga_horaria_min" class="form-label">Duração estimada (minutos)</label>
        <input type="number" class="form-control<?= classe_invalido($erros, 'carga_horaria_min') ?>"
               id="carga_horaria_min" name="carga_horaria_min" value="<?= e($valores['carga_horaria_min']) ?>" min="1" max="600">
        <?= mensagem_erro($erros, 'carga_horaria_min') ?>
    </div>
</div>

<div class="form-check mb-4">
    <input class="form-check-input" type="checkbox" id="ativo" name="ativo" value="1"<?= $valores['ativo'] === '1' ? ' checked' : '' ?>>
    <label class="form-check-label" for="ativo">Visível para os gestores</label>
</div>
