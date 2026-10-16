<ul class="menu-lista">
    <li>
        <a class="menu-link<?= menu_ativo('/admin', true) ? ' active' : '' ?>" href="<?= e(url('/admin')) ?>"><i class="bi bi-speedometer2" aria-hidden="true"></i> Painel</a>
    </li>
    <li>
        <a class="menu-link<?= menu_ativo('/admin/usuarios') ? ' active' : '' ?>" href="<?= e(url('/admin/usuarios')) ?>"><i class="bi bi-people" aria-hidden="true"></i> Usuários</a>
    </li>
    <li>
        <a class="menu-link<?= menu_ativo('/admin/trilhas') || menu_ativo('/admin/modulos') ? ' active' : '' ?>" href="<?= e(url('/admin/trilhas')) ?>"><i class="bi bi-mortarboard" aria-hidden="true"></i> Trilhas</a>
    </li>
    <li>
        <a class="menu-link<?= menu_ativo('/admin/videos') ? ' active' : '' ?>" href="<?= e(url('/admin/videos')) ?>"><i class="bi bi-camera-video" aria-hidden="true"></i> Vídeos</a>
    </li>
    <li>
        <a class="menu-link<?= menu_ativo('/admin/materiais') ? ' active' : '' ?>" href="<?= e(url('/admin/materiais')) ?>"><i class="bi bi-journal-text" aria-hidden="true"></i> Materiais</a>
    </li>
    <li>
        <a class="menu-link<?= menu_ativo('/admin/legislacao') ? ' active' : '' ?>" href="<?= e(url('/admin/legislacao')) ?>"><i class="bi bi-bank" aria-hidden="true"></i> Legislação</a>
    </li>
    <li>
        <a class="menu-link<?= menu_ativo('/admin/faq') ? ' active' : '' ?>" href="<?= e(url('/admin/faq')) ?>"><i class="bi bi-question-circle" aria-hidden="true"></i> Perguntas Frequentes</a>
    </li>
    <li>
        <a class="menu-link<?= menu_ativo('/avisos') || menu_ativo('/admin/avisos') ? ' active' : '' ?>" href="<?= e(url('/avisos')) ?>"><i class="bi bi-megaphone" aria-hidden="true"></i> Avisos</a>
    </li>
    <li>
        <a class="menu-link<?= menu_ativo('/admin/contatos') ? ' active' : '' ?>" href="<?= e(url('/admin/contatos')) ?>"><i class="bi bi-telephone" aria-hidden="true"></i> Contatos</a>
    </li>
    <li>
        <a class="menu-link<?= menu_ativo('/admin/orgaos') ? ' active' : '' ?>" href="<?= e(url('/admin/orgaos')) ?>"><i class="bi bi-building" aria-hidden="true"></i> Órgãos</a>
    </li>
</ul>
