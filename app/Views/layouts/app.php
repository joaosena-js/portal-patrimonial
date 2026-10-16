<?php
/** Layout principal (usuário autenticado). Recebe $conteudo e, opcionalmente, $titulo. */
$u = usuario_logado();
$nomeApp = (string) config('nome');
$pendentes = novidades_pendentes();
$mostrarPopup = $u !== null
    && $pendentes !== []
    && \App\Helpers\Session::obter('novidades_adiadas') !== true
    && CAMINHO_ATUAL !== '/notificacoes';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($titulo ?? 'Portal') ?> | <?= e($nomeApp) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= e(asset('css/style.css')) ?>" rel="stylesheet">
</head>
<body>
<a class="visually-hidden-focusable" href="#conteudo">Ir para o conteúdo</a>

<header class="topo">
    <div class="container topo-linha">
        <a class="marca" href="<?= e(url($u ? inicio_do_perfil($u['perfil']) : '/')) ?>">
            <span class="marca-nome"><?= e($nomeApp) ?></span>
            <span class="marca-orgao">Central de Conhecimento · CPAT/SEAD</span>
        </a>

        <?php if ($u) : ?>
            <form class="busca-global" method="get" action="<?= e(url('/busca')) ?>" role="search">
                <label class="visually-hidden" for="busca-global">Pesquisar no portal</label>
                <input type="search" id="busca-global" name="q" minlength="2" maxlength="100" required
                       placeholder="Pesquisar vídeos, materiais, legislação, perguntas...">
                <button type="submit" aria-label="Pesquisar"><i class="bi bi-search" aria-hidden="true"></i></button>
            </form>

            <div class="topo-acoes">
                <?php if ($u['perfil'] === 'gestor') : ?>
                    <a class="sino" href="<?= e(url('/notificacoes')) ?>"
                       aria-label="Novidades<?= $pendentes !== [] ? ' (' . count($pendentes) . ' não vistas)' : '' ?>">
                        <i class="bi bi-bell" aria-hidden="true"></i>
                        <?php if ($pendentes !== []) : ?>
                            <span class="sino-contador"><?= count($pendentes) > 9 ? '9+' : count($pendentes) ?></span>
                        <?php endif; ?>
                    </a>
                <?php endif; ?>

                <a class="usuario-identificacao" href="<?= e(url('/perfil')) ?>" title="Meu perfil">
                    <span class="usuario-nome"><i class="bi bi-person-circle" aria-hidden="true"></i> <?= e($u['nome']) ?></span>
                    <?php if (!empty($u['orgao_sigla'])) : ?>
                        <span class="usuario-orgao"><?= e($u['orgao_sigla']) ?></span>
                    <?php endif; ?>
                </a>

                <form method="post" action="<?= e(url('/logout')) ?>">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-sm btn-light">Sair</button>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($u) : ?>
        <nav class="menu-principal" aria-label="Menu principal">
            <div class="container">
                <?php include __DIR__ . '/' . ($u['perfil'] === 'admin' ? 'menu_admin' : 'menu_gestor') . '.php'; ?>
            </div>
        </nav>
    <?php endif; ?>
</header>

<main id="conteudo" class="container py-4">
    <?php foreach (\App\Helpers\FlashMessage::obterTodas() as $mensagem) : ?>
        <div class="alert alert-<?= e(classe_alerta($mensagem['tipo'])) ?> alert-dismissible fade show" role="alert">
            <?= e($mensagem['mensagem']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    <?php endforeach; ?>

    <?= $conteudo ?>
</main>

<footer class="rodape">
    <div class="container">
        Coordenadoria de Patrimônio - CPAT/SEAD<br>
        Versão piloto 2026
    </div>
</footer>

<?php if ($mostrarPopup) : ?>
    <?php include __DIR__ . '/../partials/novidades_modal.php'; ?>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= e(asset('js/app.js')) ?>" defer></script>
</body>
</html>
