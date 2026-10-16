<?php

declare(strict_types=1);

namespace App\Helpers;

/** Valores válidos e rótulos exibidos na interface (fonte única para validação e telas). */
final class Rotulos
{
    public const PERFIS = [
        'admin' => 'Administrador (CPAT, DBM, DBI)',
        'gestor' => 'Gestor patrimonial',
    ];

    public const SITUACOES_USUARIO = [
        'pendente' => 'Pendente de aprovação',
        'ativo' => 'Ativo',
        'inativo' => 'Inativo',
        'rejeitado' => 'Rejeitado',
    ];

    public const AREAS = [
        'geral' => 'Geral',
        'bens_moveis' => 'Bens móveis',
        'bens_imoveis' => 'Bens imóveis',
    ];

    public const SITUACOES_TRILHA = [
        'rascunho' => 'Rascunho',
        'publicada' => 'Publicada',
        'arquivada' => 'Arquivada',
    ];

    public const TIPOS_MODULO = [
        'texto' => 'Texto',
        'video' => 'Vídeo (link)',
        'documento' => 'Documento (link)',
        'link' => 'Link externo',
    ];

    /** Categorias de vídeos e de perguntas frequentes. */
    public const CATEGORIAS = [
        'cadastro' => 'Cadastro Patrimonial',
        'entrada' => 'Entrada de Materiais',
        'tombamento'=> 'Tombamento',
        'transferencia interna' => 'Transferência Interna',
        'transferencia entre orgaos' => 'Transferência Entre Órgãos',
        'inventario' => 'Inventário',
        'alienacao' => 'Alienação e Desfazimento',
        'depreciacao' => 'Depreciação',
        'virada de ano'=> 'Virada de Ano',
        'ajuri' => 'AJURI',
        'suporte e orientacoes' => 'Suporte e Orientações',
    ];

    /** Áreas responsáveis pelo conteúdo e áreas de contato. */
    public const AREAS_RESPONSAVEIS = [
        'cpat' => 'CPAT',
        'dbm' => 'DBM',
        'dbi' => 'DBI',
    ];

    public const ORIGENS_VIDEO = [
        'youtube' => 'Link do YouTube',
        'link' => 'Link institucional',
        'arquivo' => 'Arquivo de vídeo (MP4 ou WebM)',
    ];

    public const TIPOS_MATERIAL = [
        'cartilha' => 'Cartilha',
        'manual' => 'Manual',
        'checklist' => 'Checklist',
        'modelo' => 'Modelo',
        'fluxograma' => 'Fluxograma',
        'formulario' => 'Formulário',
    ];

    public const CATEGORIAS_LEGISLACAO = [
        'federal' => 'Legislação Federal',
        'estadual' => 'Legislação Estadual',
        'decreto' => 'Decretos',
        'portaria' => 'Portarias',
        'instrucao_normativa' => 'Instruções Normativas',
        'normativo_interno' => 'Normativos Internos',
        'orientacao_tecnica' => 'Orientações Técnicas',
    ];

    public const SITUACOES_LEGISLACAO = [
        'vigente' => 'Vigente',
        'revogada' => 'Revogada',
    ];

    public const TIPOS_VINCULO_FAQ = [
        'video' => 'vídeo',
        'material' => 'material',
        'legislacao' => 'legislação',
        'contato' => 'contato',
    ];

    public const TIPOS_NOTIFICACAO = [
        'video' => 'Vídeo',
        'material' => 'Material de apoio',
        'legislacao' => 'Legislação',
        'faq' => 'Pergunta frequente',
        'aviso' => 'Aviso',
    ];

    public static function de(array $mapa, ?string $chave): string
    {
        return $mapa[$chave ?? ''] ?? (string) $chave;
    }
}
