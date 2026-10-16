<?= migalhas([['Vídeos operacionais', '/videos'], [$video['titulo']]]) ?>

<div class="cabecalho-pagina">
    <h1 class="h3 mb-0"><?= e($video['titulo']) ?></h1>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <?php if ($video['origem'] === 'youtube' && !empty($video['youtube_id'])) : ?>
            <div class="ratio ratio-16x9 mb-3">
                <iframe src="https://www.youtube-nocookie.com/embed/<?= e($video['youtube_id']) ?>"
                        title="<?= e($video['titulo']) ?>" loading="lazy" allowfullscreen
                        referrerpolicy="strict-origin-when-cross-origin"></iframe>
            </div>
            <a class="btn btn-outline-primario btn-sm" href="<?= e(url_segura($video['url'])) ?>" target="_blank" rel="noopener noreferrer">
                Abrir no YouTube
            </a>
        <?php elseif ($video['origem'] === 'arquivo') : ?>
            <video class="w-100 rounded bg-dark" controls preload="metadata"
                   src="<?= e(url('/videos/arquivo', ['id' => (int) $video['id']])) ?>">
                Seu navegador não consegue reproduzir este vídeo.
            </video>
        <?php else : ?>
            <section class="painel">
                <p>Este vídeo está em um endereço institucional e abre em uma nova aba.</p>
                <a class="btn btn-primario" href="<?= e(url_segura($video['url'])) ?>" target="_blank" rel="noopener noreferrer">
                    <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i> Assistir ao vídeo
                </a>
            </section>
        <?php endif; ?>

        <?php if (!empty($video['descricao'])) : ?>
            <p class="texto-leitura mt-3"><?= nl2br(e($video['descricao'])) ?></p>
        <?php endif; ?>
    </div>

    <div class="col-lg-4">
        <section class="painel">
            <h2 class="h6">Informações</h2>
            <dl class="mb-0 dados-perfil">
                <dt>Categoria</dt><dd><?= e(rotulo('CATEGORIAS', $video['categoria'])) ?></dd>
                <dt>Área responsável</dt><dd><?= e(rotulo('AREAS_RESPONSAVEIS', $video['area_responsavel'])) ?></dd>
                <dt>Publicado em</dt><dd><?= e(data_br($video['publicado_em'])) ?></dd>
                <?php if (!empty($video['autor'])) : ?>
                    <dt>Autor</dt><dd><?= e($video['autor']) ?></dd>
                <?php endif; ?>
            </dl>
        </section>
    </div>
</div>
