<div class="coluna-formulario">
    <h1 class="h3 mb-3">Alterar senha</h1>

    <?php if ($obrigatoria) : ?>
        <div class="alert alert-warning">Sua senha atual é temporária. Defina uma nova senha para acessar o portal.</div>
    <?php endif; ?>

    <form method="post" action="<?= e(url('/conta/senha')) ?>" novalidate>
        <?= csrf_field() ?>

        <div class="mb-3">
            <label for="senha_atual" class="form-label">Senha atual</label>
            <input type="password" class="form-control<?= classe_invalido($erros, 'senha_atual') ?>" id="senha_atual"
                   name="senha_atual" autocomplete="current-password" required>
            <?= mensagem_erro($erros, 'senha_atual') ?>
        </div>

        <div class="mb-3">
            <label for="senha_nova" class="form-label">Nova senha</label>
            <input type="password" class="form-control<?= classe_invalido($erros, 'senha_nova') ?>" id="senha_nova"
                   name="senha_nova" autocomplete="new-password" required>
            <div class="form-text">Mínimo de 8 caracteres, com ao menos uma letra e um número.</div>
            <?= mensagem_erro($erros, 'senha_nova') ?>
        </div>

        <div class="mb-4">
            <label for="senha_confirmacao" class="form-label">Confirmar nova senha</label>
            <input type="password" class="form-control<?= classe_invalido($erros, 'senha_confirmacao') ?>"
                   id="senha_confirmacao" name="senha_confirmacao" autocomplete="new-password" required>
            <?= mensagem_erro($erros, 'senha_confirmacao') ?>
        </div>

        <button type="submit" class="btn btn-primario">Salvar nova senha</button>
    </form>
</div>
