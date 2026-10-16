<?= migalhas([['Contatos', '/admin/contatos'], [$contato['nome']]]) ?>

<div class="coluna-formulario">
    <h1 class="h3 mb-3"><?= e($contato['nome']) ?></h1>

    <form method="post" action="<?= e(url('/admin/contatos/atualizar')) ?>" novalidate>
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= (int) $contato['id'] ?>">

        <div class="mb-3">
            <label for="nome" class="form-label">Nome exibido</label>
            <input type="text" class="form-control<?= classe_invalido($erros, 'nome') ?>" id="nome" name="nome" value="<?= e($valores['nome']) ?>" maxlength="150" required>
            <?= mensagem_erro($erros, 'nome') ?>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="email" class="form-label">E-mail</label>
                <input type="email" class="form-control<?= classe_invalido($erros, 'email') ?>" id="email" name="email" value="<?= e($valores['email']) ?>" maxlength="190">
                <?= mensagem_erro($erros, 'email') ?>
            </div>
            <div class="col-md-6 mb-3">
                <label for="telefone" class="form-label">Telefone(s)</label>
                <input type="text" class="form-control<?= classe_invalido($erros, 'telefone') ?>" id="telefone" name="telefone" value="<?= e($valores['telefone']) ?>" maxlength="60" placeholder="(92) 0000-0000 / (92) 0000-0000">
                <?= mensagem_erro($erros, 'telefone') ?>
            </div>
        </div>

        <div class="mb-3">
            <label for="endereco" class="form-label">Endereço</label>
            <input type="text" class="form-control<?= classe_invalido($erros, 'endereco') ?>" id="endereco" name="endereco" value="<?= e($valores['endereco']) ?>" maxlength="255">
            <?= mensagem_erro($erros, 'endereco') ?>
        </div>

        <div class="mb-3">
            <label for="horario" class="form-label">Horário de atendimento</label>
            <input type="text" class="form-control<?= classe_invalido($erros, 'horario') ?>" id="horario" name="horario" value="<?= e($valores['horario']) ?>" maxlength="120">
            <?= mensagem_erro($erros, 'horario') ?>
        </div>

        <div class="mb-4">
            <label for="competencias" class="form-label">Competências</label>
            <textarea class="form-control<?= classe_invalido($erros, 'competencias') ?>" id="competencias" name="competencias" rows="5" maxlength="1500"><?= e($valores['competencias']) ?></textarea>
            <?= mensagem_erro($erros, 'competencias') ?>
        </div>

        <button type="submit" class="btn btn-primario">Salvar alterações</button>
        <a class="btn btn-link" href="<?= e(url('/admin/contatos')) ?>">Cancelar</a>
    </form>
</div>
