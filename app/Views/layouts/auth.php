<?php
/** Layout sem menu: login e páginas de erro. */
$nomeApp = (string) config('nome');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($titulo ?? 'Portal') ?> | <?= e($nomeApp) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= e(asset('css/style.css')) ?>" rel="stylesheet">
</head>
<body class="pagina-auth">
<main id="conteudo" class="auth-caixa<?= !empty($larga) ? ' auth-larga' : '' ?>">
    <div class="auth-cabecalho">
        <span class="marca-nome"><?= e($nomeApp) ?></span>
        <span class="marca-orgao">Coordenadoria de Patrimônio - SEAD</span>
    </div>

    <div class="auth-corpo">
        <?php foreach (\App\Helpers\FlashMessage::obterTodas() as $mensagem) : ?>
            <div class="alert alert-<?= e(classe_alerta($mensagem['tipo'])) ?>" role="alert">
                <?= e($mensagem['mensagem']) ?>
            </div>
        <?php endforeach; ?>

        <?= $conteudo ?>
    </div>
</main>
<script src="<?= e(asset('js/app.js')) ?>" defer></script>
</body>
</html>
