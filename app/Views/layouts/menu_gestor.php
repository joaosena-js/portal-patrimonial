<ul class="menu-lista">
    <li>
        <a class="menu-link<?= menu_ativo('/gestor', true) ? ' active' : '' ?>" href="<?= e(url('/gestor')) ?>"><i class="bi bi-house" aria-hidden="true"></i> Início</a>
    </li>
    <li>
        <a class="menu-link<?= menu_ativo('/gestor/trilha') || menu_ativo('/gestor/modulo') ? ' active' : '' ?>" href="<?= e(url('/gestor/trilhas')) ?>"><i class="bi bi-mortarboard" aria-hidden="true"></i> Trilhas</a>
    </li>
    <li>
        <a class="menu-link<?= menu_ativo('/videos') ? ' active' : '' ?>" href="<?= e(url('/videos')) ?>"><i class="bi bi-camera-video" aria-hidden="true"></i> Vídeos Operacionais</a>
    </li>
    <li>
        <a class="menu-link<?= menu_ativo('/materiais') ? ' active' : '' ?>" href="<?= e(url('/materiais')) ?>"><i class="bi bi-journal-text" aria-hidden="true"></i> Materiais de Apoio</a>
    </li>
    <li>
        <a class="menu-link<?= menu_ativo('/legislacao') ? ' active' : '' ?>" href="<?= e(url('/legislacao')) ?>"><i class="bi bi-bank" aria-hidden="true"></i> Legislação e Normativos</a>
    </li>
    <li>
        <a class="menu-link<?= menu_ativo('/faq') ? ' active' : '' ?>" href="<?= e(url('/faq')) ?>"><i class="bi bi-question-circle" aria-hidden="true"></i> Perguntas Frequentes</a>
    </li>
    <li>
        <a class="menu-link<?= menu_ativo('/avisos') ? ' active' : '' ?>" href="<?= e(url('/avisos')) ?>"><i class="bi bi-megaphone" aria-hidden="true"></i> Avisos</a>
    </li>
    <li>
        <a class="menu-link<?= menu_ativo('/contatos') ? ' active' : '' ?>" href="<?= e(url('/contatos')) ?>"><i class="bi bi-telephone" aria-hidden="true"></i> Contatos</a>
    </li>
</ul>
