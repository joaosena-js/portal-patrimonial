<?= migalhas([['Materiais de apoio']]) ?>

<div class="cabecalho-pagina">
    <div>
        <h1 class="h3 mb-1">Materiais de apoio</h1>
        <p class="text-secondary mb-0">Cartilhas, checklists, modelos e fluxogramas organizados por assunto.</p>
    </div>
</div>

<form method="get" action="<?= e(url('/materiais')) ?>" class="painel filtros mb-4" role="search">
    <div class="row g-2 align-items-end">
        <div class="col-md-6">
            <label for="q" class="form-label">Buscar</label>
            <input type="search" class="form-control" id="q" name="q" value="<?= e($filtros['q']) ?>" maxlength="100" placeholder="Título, descrição ou assunto">
        </div>
        <div class="col-md-3">
            <label for="tipo" class="form-label">Tipo</label>
            <select class="form-select" id="tipo" name="tipo">
                <option value="">Todos</option>
                <?php foreach (\App\Helpers\Rotulos::TIPOS_MATERIAL as $chave => $texto) : ?>
                    <option value="<?= e($chave) ?>"<?= $filtros['tipo'] === $chave ? ' selected' : '' ?>><?= e($texto) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-primario">Filtrar</button>
            <a class="btn btn-link" href="<?= e(url('/materiais')) ?>">Limpar</a>
        </div>
    </div>
</form>

<?php if ($grupos === []) : ?>
    <p class="text-secondary">Nenhum material encontrado.</p>
<?php endif; ?>

<div class="accordion" id="acordeaoMateriais">
    <?php foreach ($grupos as $grupoId => $grupo) : ?>
        <?php $aberto = $abrirTodos || $grupoAberto === (int) $grupoId; ?>
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button<?= $aberto ? '' : ' collapsed' ?>" type="button" data-bs-toggle="collapse"
                        data-bs-target="#grupo-<?= (int) $grupoId ?>" aria-expanded="<?= $aberto ? 'true' : 'false' ?>"
                        aria-controls="grupo-<?= (int) $grupoId ?>">
                    <?= e($grupo['titulo']) ?>
                    <span class="badge text-bg-light border ms-2"><?= count($grupo['itens']) ?></span>
                </button>
            </h2>
            <div id="grupo-<?= (int) $grupoId ?>" class="accordion-collapse collapse<?= $aberto ? ' show' : '' ?>">
                <div class="accordion-body p-0">
                    <ul class="list-unstyled mb-0">
                        <?php foreach ($grupo['itens'] as $material) : ?>
                            <li class="material-linha">
                                <i class="bi <?= e(icone_arquivo($material['arquivo_extensao'])) ?> material-icone" aria-hidden="true"></i>
                                <div class="flex-grow-1">
                                    <div class="fw-semibold">
                                        <?= e($material['titulo']) ?>
                                        <span class="badge text-bg-light border"><?= e(rotulo('TIPOS_MATERIAL', $material['tipo'])) ?></span>
                                        <?php if (eh_recente($material['publicado_em'])) : ?>
                                            <span class="badge text-bg-success">Novo</span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!empty($material['descricao'])) : ?>
                                        <div class="small text-secondary"><?= e($material['descricao']) ?></div>
                                    <?php endif; ?>
                                    <div class="small text-secondary">
                                        <?= e(strtoupper((string) $material['arquivo_extensao'])) ?> · <?= e(tamanho_legivel((int) $material['arquivo_tamanho'])) ?>
                                        · publicado em <?= e(data_br($material['publicado_em'])) ?>
                                    </div>
                                </div>
                                <a class="btn btn-sm btn-outline-primario text-nowrap" href="<?= e(url('/materiais/baixar', ['id' => (int) $material['id']])) ?>">
                                    <i class="bi bi-download" aria-hidden="true"></i> Baixar
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
