<?= migalhas([['Meu perfil']]) ?>

<div class="cabecalho-pagina">
    <h1 class="h3 mb-0">Meu perfil</h1>
    <a class="btn btn-outline-primario" href="<?= e(url('/conta/senha')) ?>"><i class="bi bi-key" aria-hidden="true"></i> Alterar senha</a>
</div>

<div class="row g-4">
    <div class="col-lg-5">
        <section class="painel">
            <h2 class="h5">Identificação</h2>
            <dl class="mb-0 dados-perfil">
                <dt>Nome</dt>
                <dd><?= e($usuario['nome']) ?></dd>
                <dt>Usuário de acesso</dt>
                <dd><?= e($usuario['usuario']) ?></dd>
                <dt>Perfil</dt>
                <dd><?= e(rotulo('PERFIS', $usuario['perfil'])) ?></dd>
                <dt>Órgão ou unidade</dt>
                <dd><?= e($usuario['orgao_sigla'] ?? 'Não vinculado') ?></dd>
                <dt>Matrícula</dt>
                <dd><?= e($usuario['matricula'] ?? 'Não informada') ?></dd>
            </dl>
            <p class="text-secondary small mt-3 mb-0">Para corrigir nome, órgão ou matrícula, fale com a CPAT.</p>
        </section>
    </div>

    <div class="col-lg-7">
        <section class="painel">
            <h2 class="h5">Contato e lotação</h2>
            <form method="post" action="<?= e(url('/perfil/atualizar')) ?>" novalidate>
                <?= csrf_field() ?>

                <div class="mb-3">
                    <label for="email" class="form-label">E-mail</label>
                    <input type="email" class="form-control<?= classe_invalido($erros, 'email') ?>" id="email" name="email"
                        value="<?= e($valores['email']) ?>" maxlength="190" required>
                    <?= mensagem_erro($erros, 'email') ?>
                </div>
                <div class="mb-3">
                    <label for="telefone" class="form-label">Telefone com DDD</label>
                    <input type="tel" class="form-control<?= classe_invalido($erros, 'telefone') ?>" id="telefone" name="telefone"
                        value="<?= e($valores['telefone']) ?>" maxlength="20">
                    <?= mensagem_erro($erros, 'telefone') ?>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="setor" class="form-label">Setor</label>
                        <input type="text" class="form-control<?= classe_invalido($erros, 'setor') ?>" id="setor" name="setor"
                            value="<?= e($valores['setor']) ?>" maxlength="120">
                        <?= mensagem_erro($erros, 'setor') ?>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="cargo" class="form-label">Cargo</label>
                        <input type="text" class="form-control<?= classe_invalido($erros, 'cargo') ?>" id="cargo" name="cargo"
                            value="<?= e($valores['cargo']) ?>" maxlength="120">
                        <?= mensagem_erro($erros, 'cargo') ?>
                    </div>
                </div>
                <button type="submit" class="btn btn-primario">Salvar alterações</button>
            </form>
        </section>
    </div>
</div>