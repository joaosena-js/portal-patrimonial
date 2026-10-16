<?= migalhas([['Contatos']]) ?>

<div class="cabecalho-pagina">
    <div>
        <h1 class="h3 mb-1">Contatos CPAT</h1>
        <p class="text-secondary mb-0">Os atendimentos relacionados à gestão patrimonial são realizados pelos departamentos responsáveis. Consulte abaixo os canais disponíveis.</p>
    </div>
</div>

<div class="row g-4">
    <?php foreach ($contatos as $contato) : ?>
        <div class="col-lg-4">
            <article class="painel h-100">
                <h2 class="h5 mb-3"><?= e($contato['nome']) ?></h2>
                <ul class="lista-contato">
                    <li>
                        <i class="bi bi-envelope" aria-hidden="true"></i>
                        <?php if (!empty($contato['email'])) : ?>
                            <a href="mailto:<?= e($contato['email']) ?>"><?= e($contato['email']) ?></a>
                        <?php else : ?>
                            <span class="text-secondary">E-mail não informado</span>
                        <?php endif; ?>
                    </li>
                    <li>
                        <i class="bi bi-telephone" aria-hidden="true"></i>
                        <?= !empty($contato['telefone']) ? e($contato['telefone']) : '<span class="text-secondary">Telefone não informado</span>' ?>
                    </li>
                    <?php if (!empty($contato['endereco'])) : ?>
                        <li><i class="bi bi-geo-alt" aria-hidden="true"></i> <?= e($contato['endereco']) ?></li>
                    <?php endif; ?>
                    <li>
                        <i class="bi bi-clock" aria-hidden="true"></i>
                        <?= !empty($contato['horario']) ? e($contato['horario']) : '<span class="text-secondary">Horário não informado</span>' ?>
                    </li>
                </ul>
                <?php if (!empty($contato['competencias'])) : ?>
                    <h3 class="h6 mt-3">Competências</h3>
                    <p class="mb-0"><?= nl2br(e($contato['competencias'])) ?></p>
                <?php endif; ?>
            </article>
        </div>
    <?php endforeach; ?>
</div>
