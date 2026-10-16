<?php
/** Paginação. Requer $paginacao = [pagina, total_paginas, caminho, query]. */
if ($paginacao['total_paginas'] > 1) :
    ?>
    <nav aria-label="Paginação">
        <ul class="pagination pagination-sm justify-content-center mb-0">
            <?php for ($i = 1; $i <= $paginacao['total_paginas']; $i++) : ?>
                <li class="page-item<?= $i === $paginacao['pagina'] ? ' active' : '' ?>">
                    <a class="page-link"
                       href="<?= e(url($paginacao['caminho'], $paginacao['query'] + ['pagina' => $i])) ?>"
                       <?= $i === $paginacao['pagina'] ? 'aria-current="page"' : '' ?>><?= $i ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
<?php endif; ?>
