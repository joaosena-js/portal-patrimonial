<?= migalhas([['Contatos']]) ?>

<div class="cabecalho-pagina">
    <div>
        <h1 class="h3 mb-1">Contatos</h1>
        <p class="text-secondary mb-0">Informações exibidas aos gestores na página de contatos.</p>
    </div>
    <a class="btn btn-outline-primario" href="<?= e(url('/contatos')) ?>">Ver como gestor</a>
</div>

<section class="painel p-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>Área</th><th>E-mail</th><th>Telefone</th><th>Horário</th><th><span class="visually-hidden">Ações</span></th></tr></thead>
            <tbody>
            <?php foreach ($contatos as $contato) : ?>
                <tr>
                    <td class="fw-semibold"><?= e($contato['nome']) ?></td>
                    <td><?= e($contato['email'] ?? '-') ?></td>
                    <td><?= e($contato['telefone'] ?? '-') ?></td>
                    <td><?= e($contato['horario'] ?? '-') ?></td>
                    <td class="text-end"><a class="btn btn-sm btn-outline-primario" href="<?= e(url('/admin/contatos/editar', ['id' => (int) $contato['id']])) ?>">Editar</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
