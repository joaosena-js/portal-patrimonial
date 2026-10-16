<?php
/** Campos compartilhados entre cadastro e edição. Variáveis: $valores, $erros, $orgaos, $usuario (opcional). */
$editando = !empty($usuario);
$proprio = $editando && (int) $usuario['id'] === (int) (usuario_logado()['id'] ?? 0);
?>
<div class="mb-3">
    <label for="nome" class="form-label">Nome completo</label>
    <input type="text" class="form-control<?= classe_invalido($erros, 'nome') ?>" id="nome" name="nome"
           value="<?= e($valores['nome']) ?>" maxlength="150" required>
    <?= mensagem_erro($erros, 'nome') ?>
</div>

<div class="mb-3">
    <label for="email" class="form-label">E-mail institucional</label>
    <input type="email" class="form-control<?= classe_invalido($erros, 'email') ?>" id="email" name="email"
           value="<?= e($valores['email']) ?>" maxlength="190" autocomplete="off" required>
    <?php if (!$editando) : ?>
        <div class="form-text">O usuário de acesso (nome.sobrenome) é gerado automaticamente a partir do nome.</div>
    <?php endif; ?>
    <?= mensagem_erro($erros, 'email') ?>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="perfil" class="form-label">Perfil</label>
        <select class="form-select<?= classe_invalido($erros, 'perfil') ?>" id="perfil" name="perfil"<?= $proprio ? ' disabled' : '' ?>>
            <?php foreach (\App\Helpers\Rotulos::PERFIS as $chave => $texto) : ?>
                <option value="<?= e($chave) ?>"<?= $valores['perfil'] === $chave ? ' selected' : '' ?>><?= e($texto) ?></option>
            <?php endforeach; ?>
        </select>
        <?= mensagem_erro($erros, 'perfil') ?>
    </div>

    <div class="col-md-6 mb-3">
        <label for="orgao_id" class="form-label">Órgão ou unidade</label>
        <select class="form-select<?= classe_invalido($erros, 'orgao_id') ?>" id="orgao_id" name="orgao_id">
            <option value="">Selecione</option>
            <?php foreach ($orgaos as $orgao) : ?>
                <option value="<?= (int) $orgao['id'] ?>"<?= (string) $valores['orgao_id'] === (string) $orgao['id'] ? ' selected' : '' ?>>
                    <?= e($orgao['sigla']) ?> - <?= e($orgao['nome']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?= mensagem_erro($erros, 'orgao_id') ?>
    </div>
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

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="matricula" class="form-label">Matrícula</label>
        <input type="text" class="form-control<?= classe_invalido($erros, 'matricula') ?>" id="matricula" name="matricula"
               value="<?= e($valores['matricula']) ?>" maxlength="30">
        <?= mensagem_erro($erros, 'matricula') ?>
    </div>
    <div class="col-md-6 mb-3">
        <label for="telefone" class="form-label">Telefone com DDD</label>
        <input type="tel" class="form-control<?= classe_invalido($erros, 'telefone') ?>" id="telefone" name="telefone"
               value="<?= e($valores['telefone']) ?>" maxlength="20">
        <?= mensagem_erro($erros, 'telefone') ?>
    </div>
</div>

<?php if ($editando) : ?>
    <div class="mb-3">
        <label for="situacao" class="form-label">Situação</label>
        <select class="form-select<?= classe_invalido($erros, 'situacao') ?>" id="situacao" name="situacao"<?= $proprio ? ' disabled' : '' ?>>
            <?php foreach (\App\Helpers\Rotulos::SITUACOES_USUARIO as $chave => $texto) : ?>
                <option value="<?= e($chave) ?>"<?= $valores['situacao'] === $chave ? ' selected' : '' ?>><?= e($texto) ?></option>
            <?php endforeach; ?>
        </select>
        <?= mensagem_erro($erros, 'situacao') ?>
        <?php if ($proprio) : ?>
            <div class="form-text">Você não pode alterar o próprio perfil nem a própria situação.</div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<div class="mb-4">
    <label for="senha" class="form-label"><?= $editando ? 'Nova senha temporária (opcional)' : 'Senha temporária' ?></label>
    <input type="password" class="form-control<?= classe_invalido($erros, 'senha') ?>" id="senha" name="senha"
           autocomplete="new-password"<?= $editando ? '' : ' required' ?>>
    <div class="form-text">
        Mínimo de 8 caracteres, com letra e número. O usuário será obrigado a trocá-la no próximo acesso.
        <?= $editando ? 'Deixe em branco para manter a senha atual.' : '' ?>
    </div>
    <?= mensagem_erro($erros, 'senha') ?>
</div>
