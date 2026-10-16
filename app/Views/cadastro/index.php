<h1 class="h4 mb-1"><?= $exigeAprovacao ? 'Solicitar cadastro' : 'Criar conta' ?></h1>
<p class="text-secondary small mb-4">
    <?php if ($exigeAprovacao) : ?>
        Preencha seus dados. Após a aprovação da CPAT, você receberá um e-mail e poderá acessar o portal
        com o usuário no formato <strong>nome.sobrenome</strong>.
    <?php else : ?>
        Preencha seus dados para acessar o portal. Seu usuário de acesso terá o formato
        <strong>nome.sobrenome</strong> e será gerado automaticamente.
    <?php endif; ?>
</p>

<form method="post" action="<?= e(url('/cadastro/salvar')) ?>" novalidate autocomplete="off">
    <?= csrf_field() ?>

    <!-- Campo-armadilha contra robôs: deve permanecer vazio. -->
    <div class="campo-armadilha" aria-hidden="true">
        <label for="website">Não preencha este campo</label>
        <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
    </div>

    <div class="mb-3">
        <label for="nome" class="form-label">Nome completo</label>
        <input type="text" class="form-control<?= classe_invalido($erros, 'nome') ?>" id="nome" name="nome"
            value="<?= e($valores['nome']) ?>" maxlength="150" data-gerar-usuario required>
        <div class="form-text">Seu usuário de acesso será: <strong data-usuario-preview>nome.sobrenome</strong></div>
        <?= mensagem_erro($erros, 'nome') ?>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="cpf" class="form-label">CPF</label>
            <input type="text" class="form-control<?= classe_invalido($erros, 'cpf') ?>" id="cpf" name="cpf"
                value="<?= e($valores['cpf']) ?>" maxlength="14" inputmode="numeric" placeholder="000.000.000-00" required>
            <div class="form-text"></div>
            <?= mensagem_erro($erros, 'cpf') ?>
        </div>
        <div class="col-md-6 mb-3">
            <label for="matricula" class="form-label">Matrícula (se houver)</label>
            <input type="text" class="form-control<?= classe_invalido($erros, 'matricula') ?>" id="matricula" name="matricula"
                value="<?= e($valores['matricula']) ?>" maxlength="30">
            <?= mensagem_erro($erros, 'matricula') ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="orgao_id" class="form-label">Órgão</label>
            <select class="form-select<?= classe_invalido($erros, 'orgao_id') ?>" id="orgao_id" name="orgao_id" required>
                <option value="">Selecione</option>
                <?php foreach ($orgaos as $orgao) : ?>
                    <option value="<?= (int) $orgao['id'] ?>" <?= (string) $valores['orgao_id'] === (string) $orgao['id'] ? ' selected' : '' ?>>
                        <?= e($orgao['sigla']) ?> - <?= e($orgao['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?= mensagem_erro($erros, 'orgao_id') ?>
        </div>
        <div class="col-md-6 mb-3">
            <label for="setor" class="form-label">Setor</label>
            <input type="text" class="form-control<?= classe_invalido($erros, 'setor') ?>" id="setor" name="setor"
                value="<?= e($valores['setor']) ?>" maxlength="120" required>
            <?= mensagem_erro($erros, 'setor') ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-7 mb-3">
            <label for="email" class="form-label">E-mail</label>
            <input type="email" class="form-control<?= classe_invalido($erros, 'email') ?>" id="email" name="email"
                value="<?= e($valores['email']) ?>" maxlength="190" required>
            <?= mensagem_erro($erros, 'email') ?>
        </div>
        <div class="col-md-5 mb-3">
            <label for="telefone" class="form-label">Telefone com DDD</label>
            <input type="tel" class="form-control<?= classe_invalido($erros, 'telefone') ?>" id="telefone" name="telefone"
                value="<?= e($valores['telefone']) ?>" maxlength="20" inputmode="tel" required>
            <?= mensagem_erro($erros, 'telefone') ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="senha" class="form-label">Senha</label>
            <input type="password" class="form-control<?= classe_invalido($erros, 'senha') ?>" id="senha" name="senha"
                autocomplete="new-password" required>
            <div class="form-text">Mínimo de 8 caracteres, com letra e número.</div>
            <?= mensagem_erro($erros, 'senha') ?>
        </div>
        <div class="col-md-6 mb-4">
            <label for="senha_confirmacao" class="form-label">Confirmar senha</label>
            <input type="password" class="form-control<?= classe_invalido($erros, 'senha_confirmacao') ?>" id="senha_confirmacao"
                name="senha_confirmacao" autocomplete="new-password" required>
            <?= mensagem_erro($erros, 'senha_confirmacao') ?>
        </div>
    </div>

    <button type="submit" class="btn btn-primario"><?= $exigeAprovacao ? 'Enviar solicitação' : 'Criar conta' ?></button>
    <a class="btn btn-link" href="<?= e(url('/login')) ?>">Voltar ao login</a>
</form>