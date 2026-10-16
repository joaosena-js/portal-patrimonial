<?php
/**
 * Pop-up de novidades, exibido ao gestor com conteúdos ainda não visualizados.
 * Requer $pendentes (lista de notificações não lidas).
 */
$total = count($pendentes);
$exibidas = array_slice($pendentes, 0, 6);
?>
<div class="modal fade" id="modalNovidades" tabindex="-1" aria-labelledby="modalNovidadesTitulo" aria-hidden="true"
     data-modal-novidades data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title h5" id="modalNovidadesTitulo">
                    <i class="bi bi-bell" aria-hidden="true"></i> Há novidades no portal
                </h2>
            </div>
            <div class="modal-body">
                <p class="mb-3">Novos conteúdos foram adicionados desde sua última visita.</p>
                <ul class="lista-novidades-modal">
                    <?php foreach ($exibidas as $item) : ?>
                        <li>
                            <i class="bi <?= e(icone_notificacao($item['tipo'])) ?>" aria-hidden="true"></i>
                            <span><?= e($item['titulo']) ?></span>
                            <?php if ((int) $item['urgente'] === 1) : ?><span class="badge text-bg-danger">Urgente</span><?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <?php if ($total > count($exibidas)) : ?>
                    <p class="small text-secondary mb-0">e mais <?= (int) ($total - count($exibidas)) ?> novidade(s).</p>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <form method="post" action="<?= e(url('/notificacoes/adiar')) ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="voltar" value="<?= e(CAMINHO_ATUAL) ?>">
                    <button type="submit" class="btn btn-outline-primario">Lembrar depois</button>
                </form>
                <a class="btn btn-primario" href="<?= e(url('/notificacoes')) ?>">Visualizar agora</a>
            </div>
        </div>
    </div>
</div>
