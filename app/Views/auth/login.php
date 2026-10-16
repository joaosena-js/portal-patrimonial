<h1 class="h4 mb-1">Acesso ao portal</h1>
<p class="text-secondary small mb-4">Entre com seu usuário e sua senha.</p>

<form method="post" action="<?= e(url('/login')) ?>" autocomplete="on">
    <?= csrf_field() ?>

    <div class="mb-3">
        <label for="usuario" class="form-label">Usuário</label>
        <input type="text" class="form-control" id="usuario" name="usuario" value="<?= e($usuario) ?>"
            maxlength="60" placeholder="nome.sobrenome" autocomplete="username" autocapitalize="none" required autofocus>
    </div>

    <div class="mb-4">
        <label for="senha" class="form-label">Senha</label>
        <input type="password" class="form-control" id="senha" name="senha"
            autocomplete="current-password" required>
    </div>

    <button type="submit" class="btn btn-primario w-100">Entrar</button>

    <p class="text-center small mt-3 mb-0">
        Primeiro acesso? <a href="<?= e(url('/cadastro')) ?>"><?= config('cadastro_requer_aprovacao', false) ? 'Solicite seu cadastro' : 'Crie sua conta' ?></a>
    </p>
</form>