<?= migalhas([['Perguntas frequentes']]) ?>

<div class="cabecalho-pagina">
    <div>
        <h1 class="h3 mb-1">Perguntas frequentes</h1>
        <p class="text-secondary mb-0">Respostas rápidas, com atalhos para vídeos, materiais e legislação relacionados.</p>
    </div>
</div>

<form method="get" action="<?= e(url('/faq')) ?>" class="painel filtros mb-4" role="search">
    <div class="row g-2 align-items-end">
        <div class="col-md-6">
            <label for="q" class="form-label">Pesquisar</label>
            <input type="search" class="form-control" id="q" name="q" value="<?= e($filtros['q']) ?>" maxlength="100" placeholder="Palavra da pergunta ou da resposta">
        </div>
        <div class="col-md-4">
            <label for="categoria" class="form-label">Categoria</label>
            <select class="form-select" id="categoria" name="categoria">
                <option value="">Todas</option>
                <?php foreach (\App\Helpers\Rotulos::CATEGORIAS as $chave => $texto) : ?>
                    <option value="<?= e($chave) ?>"<?= $filtros['categoria'] === $chave ? ' selected' : '' ?>><?= e($texto) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-primario">Pesquisar</button>
            <a class="btn btn-link" href="<?= e(url('/faq')) ?>">Limpar</a>
        </div>
    </div>
</form>

<?php if ($total === 0) : ?>
    <p class="text-secondary">Nenhuma pergunta encontrada.</p>
<?php endif; ?>

<?php foreach ($porCategoria as $categoria => $perguntas) : ?>
    <section class="mb-4">
        <h2 class="h5 mb-2"><?= e(rotulo('CATEGORIAS', $categoria)) ?></h2>

        <div class="accordion" id="faq-<?= e($categoria) ?>">
            <?php foreach ($perguntas as $pergunta) : ?>
                <?php
                $faqId = (int) $pergunta['id'];
                $links = $vinculos[$faqId] ?? [];
                // Com pesquisa ativa, as respostas já aparecem abertas.
                $aberta = $filtros['q'] !== '';
                ?>
                <div class="accordion-item">
                    <h3 class="accordion-header">
                        <button class="accordion-button<?= $aberta ? '' : ' collapsed' ?>" type="button"
                                data-bs-toggle="collapse" data-bs-target="#faq-item-<?= $faqId ?>"
                                aria-expanded="<?= $aberta ? 'true' : 'false' ?>" aria-controls="faq-item-<?= $faqId ?>">
                            <?= e($pergunta['pergunta']) ?>
                            <?php if (eh_recente($pergunta['criado_em'])) : ?><span class="badge text-bg-success ms-2">Novo</span><?php endif; ?>
                        </button>
                    </h3>
                    <div id="faq-item-<?= $faqId ?>" class="accordion-collapse collapse<?= $aberta ? ' show' : '' ?>">
                        <div class="accordion-body">
                            <div class="texto-leitura"><?= nl2br(e($pergunta['resposta'])) ?></div>

                            <?php if ($links !== []) : ?>
                                <div class="faq-relacionados">
                                    <div class="small fw-semibold mb-1">Veja também</div>
                                    <ul class="list-unstyled mb-0">
                                        <?php foreach ($links as $v) : ?>
                                            <?php $externo = $v['tipo'] === 'legislacao' && empty($v['legislacao_arquivo']) && !empty($v['legislacao_url']); ?>
                                            <li>
                                                <i class="bi <?= e(icone_vinculo((string) $v['tipo'])) ?>" aria-hidden="true"></i>
                                                <a href="<?= e(url_vinculo($v)) ?>"<?= $externo ? ' target="_blank" rel="noopener noreferrer"' : '' ?>><?= e($v['titulo']) ?></a>
                                                <span class="text-secondary small">(<?= e(rotulo('TIPOS_VINCULO_FAQ', (string) $v['tipo'])) ?>)</span>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
<?php endforeach; ?>
