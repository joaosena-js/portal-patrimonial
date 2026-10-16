<?php

declare(strict_types=1);

/**
 * Funções de apoio usadas pelas views e controllers.
 * Caminhos de URL devem sempre passar por url() para funcionar em subpastas (ex.: XAMPP).
 */

use App\Core\Config;
use App\Helpers\Auth;
use App\Helpers\Csrf;
use App\Helpers\Rotulos;

/** Escapa texto para saída em HTML. Use em toda variável impressa nas views. */
function e(mixed $valor): string
{
    return htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Monta uma URL interna, com query string opcional. */
function url(string $caminho = '/', array $query = []): string
{
    $url = BASE_URL . '/' . ltrim($caminho, '/');

    return $query === [] ? $url : $url . '?' . http_build_query($query);
}

function asset(string $arquivo): string
{
    return BASE_URL . '/assets/' . ltrim($arquivo, '/');
}

function config(string $chave, mixed $padrao = null): mixed
{
    return Config::obter($chave, $padrao);
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(Csrf::token()) . '">';
}

/** @return array<string, mixed>|null */
function usuario_logado(): ?array
{
    return Auth::usuario();
}

function inicio_do_perfil(string $perfil): string
{
    return $perfil === 'admin' ? '/admin' : '/gestor';
}

/** Indica se o caminho atual pertence ao item de menu. */
function menu_ativo(string $caminho, bool $exato = false): bool
{
    if ($exato) {
        return CAMINHO_ATUAL === $caminho;
    }

    return str_starts_with(CAMINHO_ATUAL, $caminho);
}

/** Rótulo de uma lista de Rotulos (ex.: rotulo('AREAS', 'bens_moveis')). */
function rotulo(string $lista, ?string $chave): string
{
    return Rotulos::de(constant(Rotulos::class . '::' . $lista), $chave);
}

function data_br(?string $data, bool $comHora = false): string
{
    if ($data === null || $data === '') {
        return '';
    }

    $tempo = strtotime($data);

    return $tempo === false ? '' : date($comHora ? 'd/m/Y H:i' : 'd/m/Y', $tempo);
}

/** Devolve a URL somente se for http/https. Defesa extra contra links javascript: salvos no banco. */
function url_segura(?string $url): string
{
    $esquema = strtolower((string) parse_url((string) $url, PHP_URL_SCHEME));

    return in_array($esquema, ['http', 'https'], true) ? (string) $url : '#';
}

function classe_invalido(array $erros, string $campo): string
{
    return isset($erros[$campo]) ? ' is-invalid' : '';
}

function mensagem_erro(array $erros, string $campo): string
{
    return isset($erros[$campo])
        ? '<div class="invalid-feedback">' . e($erros[$campo]) . '</div>'
        : '';
}

function classe_alerta(string $tipo): string
{
    return match ($tipo) {
        'sucesso' => 'success',
        'erro' => 'danger',
        'aviso' => 'warning',
        default => 'info',
    };
}

/** Texto do andamento de uma trilha para o gestor. */
function texto_andamento(?string $situacaoInscricao): string
{
    return match ($situacaoInscricao) {
        'concluida' => 'Concluída',
        'em_andamento' => 'Em andamento',
        default => 'Não iniciada',
    };
}

function classe_andamento(?string $situacaoInscricao): string
{
    return match ($situacaoInscricao) {
        'concluida' => 'text-bg-success',
        'em_andamento' => 'text-bg-primary',
        default => 'text-bg-light border',
    };
}

/** URL completa (com domínio), para links enviados por e-mail. Prefere APP_URL do .env. */
function url_absoluta(string $caminho = '/', array $query = []): string
{
    $origem = (string) config('app_url', '');

    if ($origem === '') {
        $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        $host = (string) ($_SERVER['HTTP_HOST'] ?? 'localhost');
        $host = preg_match('/^[A-Za-z0-9.\-:]+$/', $host) === 1 ? $host : 'localhost';
        $origem = ($https ? 'https' : 'http') . '://' . $host;
    }

    return $origem . url($caminho, $query);
}

/** Conteúdo publicado há poucos dias recebe o selo "Novo". */
function eh_recente(?string $data, int $dias = 14): bool
{
    $tempo = $data ? strtotime($data) : false;

    return $tempo !== false && $tempo >= strtotime('-' . $dias . ' days');
}

function tamanho_legivel(int $bytes): string
{
    if ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 1, ',', '.') . ' MB';
    }

    return max(1, (int) round($bytes / 1024)) . ' KB';
}

/** Ícone (Bootstrap Icons) conforme a extensão do arquivo. */
function icone_arquivo(?string $extensao): string
{
    return match ($extensao) {
        'pdf' => 'bi-file-earmark-pdf',
        'docx' => 'bi-file-earmark-word',
        'xlsx' => 'bi-file-earmark-excel',
        'mp4', 'webm' => 'bi-camera-video',
        default => 'bi-file-earmark',
    };
}

/**
 * Trilha de navegação (breadcrumb). Cada item é [rótulo, caminho]; o último não precisa de caminho.
 * O primeiro item "Início" é adicionado automaticamente.
 */
function migalhas(array $itens): string
{
    $usuario = usuario_logado();
    $html = '<nav aria-label="Trilha de navegação" class="migalhas"><ol class="breadcrumb">';

    if ($usuario !== null) {
        $html .= '<li class="breadcrumb-item"><a href="' . e(url(inicio_do_perfil((string) $usuario['perfil']))) . '">Início</a></li>';
    }

    $ultimo = count($itens) - 1;

    foreach (array_values($itens) as $posicao => $item) {
        if ($posicao === $ultimo || empty($item[1])) {
            $html .= '<li class="breadcrumb-item active" aria-current="page">' . e($item[0]) . '</li>';
        } else {
            $html .= '<li class="breadcrumb-item"><a href="' . e(url($item[1])) . '">' . e($item[0]) . '</a></li>';
        }
    }

    return $html . '</ol></nav>';
}

/** Ícone (Bootstrap Icons) de cada tipo de novidade. */
function icone_notificacao(string $tipo): string
{
    return match ($tipo) {
        'video' => 'bi-camera-video',
        'material' => 'bi-journal-text',
        'legislacao' => 'bi-bank',
        'faq' => 'bi-question-circle',
        default => 'bi-megaphone',
    };
}

/** Página onde a novidade pode ser vista. */
function url_notificacao(string $tipo, ?int $entidadeId): string
{
    return match ($tipo) {
        'video' => $entidadeId !== null ? url('/videos/ver', ['id' => $entidadeId]) : url('/videos'),
        'material' => url('/materiais'),
        'legislacao' => url('/legislacao'),
        'faq' => url('/faq'),
        default => url('/avisos'),
    };
}

/**
 * Novidades que o gestor ainda não visualizou (alimentam o sino e o pop-up).
 * Só os gestores recebem; a consulta roda uma vez por requisição.
 *
 * @return list<array<string, mixed>>
 */
function novidades_pendentes(): array
{
    static $cache = null;

    if ($cache !== null) {
        return $cache;
    }

    $usuario = Auth::usuario();

    if ($usuario === null || $usuario['perfil'] !== 'gestor') {
        return $cache = [];
    }

    return $cache = (new \App\Models\Notificacao())->naoLidas((int) $usuario['id'], 50);
}

/** Ícone de cada tipo de vínculo de uma pergunta frequente. */
function icone_vinculo(string $tipo): string
{
    return match ($tipo) {
        'video' => 'bi-camera-video',
        'material' => 'bi-journal-text',
        'legislacao' => 'bi-bank',
        default => 'bi-telephone',
    };
}

/** Destino de um vínculo (item devolvido por Faq::vinculosDetalhados). */
function url_vinculo(array $vinculo): string
{
    $id = (int) $vinculo['referencia_id'];

    return match ($vinculo['tipo']) {
        'video' => url('/videos/ver', ['id' => $id]),
        'material' => url('/materiais/baixar', ['id' => $id]),
        'legislacao' => !empty($vinculo['legislacao_arquivo'])
            ? url('/legislacao/baixar', ['id' => $id])
            : (!empty($vinculo['legislacao_url']) ? url_segura((string) $vinculo['legislacao_url']) : url('/legislacao')),
        default => url('/contatos'),
    };
}
