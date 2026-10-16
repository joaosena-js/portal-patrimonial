<h1 class="h4">Não foi possível concluir a operação</h1>
<p class="text-secondary">Ocorreu um erro interno. O problema foi registrado. Tente novamente em instantes e, se persistir, informe a CPAT.</p>
<?php if (!empty($detalhe)) : ?>
    <pre class="detalhe-erro"><?= e($detalhe) ?></pre>
<?php endif; ?>
<a class="btn btn-primario" href="<?= e(url('/')) ?>">Voltar ao início</a>
