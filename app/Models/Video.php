<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/** Vídeos operacionais (YouTube, link institucional ou arquivo enviado). */
final class Video extends Model
{
    private const COLUNAS = 'v.id, v.titulo, v.descricao, v.categoria, v.area_responsavel, v.origem, v.url,
                             v.youtube_id, v.arquivo_id, v.publicado_em, v.ativo, a.nome AS autor';

    /** @return array<string, mixed>|null */
    public function buscarPorId(int $id): ?array
    {
        return $this->consultarUm(
            'SELECT ' . self::COLUNAS . '
             FROM videos v LEFT JOIN usuarios a ON a.id = v.autor_id
             WHERE v.id = :id LIMIT 1',
            ['id' => $id]
        );
    }

    /**
     * Vídeos ativos, com busca por texto, categoria e ordenação.
     *
     * @return list<array<string, mixed>>
     */
    public function listarPublicos(string $busca, string $categoria, string $ordem): array
    {
        $condicoes = ['v.ativo = 1'];
        $parametros = [];

        if ($busca !== '') {
            $like = '%' . addcslashes($busca, '%_\\') . '%';
            $condicoes[] = '(v.titulo LIKE :busca_titulo OR v.descricao LIKE :busca_descricao)';
            $parametros += ['busca_titulo' => $like, 'busca_descricao' => $like];
        }

        if ($categoria !== '') {
            $condicoes[] = 'v.categoria = :categoria';
            $parametros['categoria'] = $categoria;
        }

        $ordenacao = match ($ordem) {
            'antigos' => 'v.publicado_em ASC, v.id ASC',
            'titulo' => 'v.titulo ASC',
            default => 'v.publicado_em DESC, v.id DESC',
        };

        return $this->consultar(
            'SELECT ' . self::COLUNAS . '
             FROM videos v LEFT JOIN usuarios a ON a.id = v.autor_id
             WHERE ' . implode(' AND ', $condicoes) . '
             ORDER BY ' . $ordenacao . '
             LIMIT 60',
            $parametros
        );
    }

    /** @return list<array<string, mixed>> */
    public function listarParaAdmin(): array
    {
        return $this->consultar(
            'SELECT ' . self::COLUNAS . '
             FROM videos v LEFT JOIN usuarios a ON a.id = v.autor_id
             ORDER BY v.publicado_em DESC, v.id DESC'
        );
    }

    /**
     * @param array{titulo: string, descricao: ?string, categoria: string, area_responsavel: string, origem: string,
     *              url: ?string, youtube_id: ?string, arquivo_id: ?int, publicado_em: string, ativo: int, autor_id: int} $dados
     */
    public function criar(array $dados): int
    {
        $this->executar(
            'INSERT INTO videos
                (titulo, descricao, categoria, area_responsavel, origem, url, youtube_id, arquivo_id, publicado_em, ativo, autor_id)
             VALUES
                (:titulo, :descricao, :categoria, :area_responsavel, :origem, :url, :youtube_id, :arquivo_id, :publicado_em, :ativo, :autor_id)',
            $dados
        );

        return $this->ultimoId();
    }

    /**
     * @param array{titulo: string, descricao: ?string, categoria: string, area_responsavel: string, origem: string,
     *              url: ?string, youtube_id: ?string, arquivo_id: ?int, publicado_em: string, ativo: int} $dados
     */
    public function atualizar(int $id, array $dados): void
    {
        $this->executar(
            'UPDATE videos
             SET titulo = :titulo, descricao = :descricao, categoria = :categoria, area_responsavel = :area_responsavel,
                 origem = :origem, url = :url, youtube_id = :youtube_id, arquivo_id = :arquivo_id,
                 publicado_em = :publicado_em, ativo = :ativo
             WHERE id = :id',
            $dados + ['id' => $id]
        );
    }

    public function contarAtivos(): int
    {
        return (int) ($this->consultarUm('SELECT COUNT(*) AS total FROM videos WHERE ativo = 1')['total'] ?? 0);
    }
}
