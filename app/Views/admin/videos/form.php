<?php
$editando = !empty($video);
$origem = $valores['origem'];
?>
<?= migalhas([['Vídeos operacionais', '/admin/videos'], [$titulo]]) ?>

<div class="coluna-formulario">
    <h1 class="h3 mb-3"><?= e($titulo) ?></h1>

    <form method="post" action="<?= e(url($editando ? '/admin/videos/atualizar' : '/admin/videos/salvar')) ?>" enctype="multipart/form-data" novalidate>
        <?= csrf_field() ?>
        <?php if ($editando) : ?><input type="hidden" name="id" value="<?= (int) $video['id'] ?>"><?php endif; ?>

        <div class="mb-3">
            <label for="titulo" class="form-label">Título</label>
            <input type="text" class="form-control<?= classe_invalido($erros, 'titulo') ?>" id="titulo" name="titulo" value="<?= e($valores['titulo']) ?>" maxlength="150" required>
            <?= mensagem_erro($erros, 'titulo') ?>
        </div>

        <div class="mb-3">
            <label for="descricao" class="form-label">Descrição</label>
            <textarea class="form-control<?= classe_invalido($erros, 'descricao') ?>" id="descricao" name="descricao" rows="3" maxlength="1000"><?= e($valores['descricao']) ?></textarea>
            <?= mensagem_erro($erros, 'descricao') ?>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="categoria" class="form-label">Categoria</label>
                <select class="form-select<?= classe_invalido($erros, 'categoria') ?>" id="categoria" name="categoria">
                    <?php foreach (\App\Helpers\Rotulos::CATEGORIAS as $chave => $texto) : ?>
                        <option value="<?= e($chave) ?>"<?= $valores['categoria'] === $chave ? ' selected' : '' ?>><?= e($texto) ?></option>
                    <?php endforeach; ?>
                </select>
                <?= mensagem_erro($erros, 'categoria') ?>
            </div>
            <div class="col-md-6 mb-3">
                <label for="area_responsavel" class="form-label">Área responsável</label>
                <select class="form-select<?= classe_invalido($erros, 'area_responsavel') ?>" id="area_responsavel" name="area_responsavel">
                    <?php foreach (\App\Helpers\Rotulos::AREAS_RESPONSAVEIS as $chave => $texto) : ?>
                        <option value="<?= e($chave) ?>"<?= $valores['area_responsavel'] === $chave ? ' selected' : '' ?>><?= e($texto) ?></option>
                    <?php endforeach; ?>
                </select>
                <?= mensagem_erro($erros, 'area_responsavel') ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="origem" class="form-label">Como o vídeo será disponibilizado</label>
                <select class="form-select<?= classe_invalido($erros, 'origem') ?>" id="origem" name="origem" data-controla-campos>
                    <?php foreach (\App\Helpers\Rotulos::ORIGENS_VIDEO as $chave => $texto) : ?>
                        <option value="<?= e($chave) ?>"<?= $origem === $chave ? ' selected' : '' ?>><?= e($texto) ?></option>
                    <?php endforeach; ?>
                </select>
                <?= mensagem_erro($erros, 'origem') ?>
            </div>
            <div class="col-md-6 mb-3">
                <label for="publicado_em" class="form-label">Data de publicação</label>
                <input type="date" class="form-control<?= classe_invalido($erros, 'publicado_em') ?>" id="publicado_em" name="publicado_em" value="<?= e($valores['publicado_em']) ?>" required>
                <?= mensagem_erro($erros, 'publicado_em') ?>
            </div>
        </div>

        <div class="mb-3<?= in_array($origem, ['youtube', 'link'], true) ? '' : ' d-none' ?>" data-mostrar-quando="youtube,link">
            <label for="url" class="form-label">Endereço do vídeo</label>
            <input type="url" class="form-control<?= classe_invalido($erros, 'url') ?>" id="url" name="url" value="<?= e($valores['url']) ?>" maxlength="500" placeholder="https://">
            <div class="form-text">YouTube: cole o endereço normal do vídeo. Institucional: o vídeo abrirá em nova aba.</div>
            <?= mensagem_erro($erros, 'url') ?>
        </div>

        <div class="mb-3<?= $origem === 'arquivo' ? '' : ' d-none' ?>" data-mostrar-quando="arquivo">
            <label for="arquivo" class="form-label">Arquivo de vídeo (MP4 ou WebM)</label>
            <input type="file" class="form-control<?= classe_invalido($erros, 'arquivo') ?>" id="arquivo" name="arquivo" accept=".mp4,.webm,video/mp4,video/webm">
            <div class="form-text">
                Limite do portal: <?= (int) $limiteMb ?> MB. O PHP também limita o envio (upload_max_filesize e post_max_size no php.ini).
                <?php if ($editando && $video['origem'] === 'arquivo' && $video['arquivo_id'] !== null) : ?>
                    Já existe um arquivo enviado. Selecione outro apenas para substituí-lo.
                <?php endif; ?>
            </div>
            <?= mensagem_erro($erros, 'arquivo') ?>
        </div>

        <div class="form-check mb-4">
            <input class="form-check-input" type="checkbox" id="ativo" name="ativo" value="1"<?= $valores['ativo'] === '1' ? ' checked' : '' ?>>
            <label class="form-check-label" for="ativo">Visível para os gestores</label>
        </div>

        <button type="submit" class="btn btn-primario"><?= $editando ? 'Salvar alterações' : 'Publicar vídeo' ?></button>
        <a class="btn btn-link" href="<?= e(url('/admin/videos')) ?>">Cancelar</a>
    </form>
</div>
