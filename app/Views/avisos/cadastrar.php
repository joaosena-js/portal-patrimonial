<div class="coluna-formulario">
    <nav class="mb-2"><a class="small" href="<?= e(url('/avisos')) ?>">Voltar ao quadro de avisos</a></nav>
    <h1 class="h3 mb-3">Novo aviso</h1>

    <form method="post" action="<?= e(url('/admin/avisos/salvar')) ?>" novalidate>
        <?= csrf_field() ?>

        <div class="mb-3">
            <label for="titulo" class="form-label">Título</label>
            <input type="text" class="form-control<?= classe_invalido($erros, 'titulo') ?>" id="titulo" name="titulo"
                   value="<?= e($valores['titulo']) ?>" maxlength="150" required>
            <?= mensagem_erro($erros, 'titulo') ?>
        </div>

        <div class="mb-3">
            <label for="mensagem" class="form-label">Mensagem</label>
            <textarea class="form-control<?= classe_invalido($erros, 'mensagem') ?>" id="mensagem" name="mensagem"
                      rows="6" maxlength="2000" required><?= e($valores['mensagem']) ?></textarea>
            <?= mensagem_erro($erros, 'mensagem') ?>
        </div>

        <div class="mb-4">
            <label for="expira_em" class="form-label">Válido até (opcional)</label>
            <input type="date" class="form-control<?= classe_invalido($erros, 'expira_em') ?>" id="expira_em"
                   name="expira_em" value="<?= e($valores['expira_em']) ?>">
            <div class="form-text">Sem data, o aviso permanece até ser retirado do quadro.</div>
            <?= mensagem_erro($erros, 'expira_em') ?>
        </div>

        <div class="form-check mb-4">
            <input class="form-check-input" type="checkbox" id="urgente" name="urgente" value="1"<?= ($valores['urgente'] ?? '0') === '1' ? ' checked' : '' ?>>
            <label class="form-check-label" for="urgente">Aviso urgente (recebe destaque e aparece primeiro)</label>
        </div>

        <button type="submit" class="btn btn-primario">Publicar aviso</button>
        <a class="btn btn-link" href="<?= e(url('/avisos')) ?>">Cancelar</a>
    </form>
</div>
