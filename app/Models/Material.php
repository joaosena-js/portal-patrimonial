<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/** Materiais de apoio (cartilhas, manuais, checklists, modelos, fluxogramas e formulários). */
final class Material extends Model
{
    private const COLUNAS = 'm.id, m.grupo_id, m.titulo, m.descricao, m.tipo, m.arquivo_id, m.publicado_em, m.ativo,
                             g.titulo AS grupo_titulo, f.extensao AS arquivo_extensao, f.tamanho AS arquivo_tamanho,
                             f.nome_original AS arquivo_nome';

    /** @return array<string, mixed>|null */
    public function buscarPorId(int $id): ?array
    {
        return $this->consultarUm(
            'SELECT ' . self::COLUNAS . '
             FROM materiais m
             JOIN material_grupos g ON g.id = m.grupo_id
             JOIN arquivos f ON f.id = m.arquivo_id
             WHERE m.id = :id LIMIT 1',
            ['id' => $id]
        );
    }

    /**
     * Materiais ativos de grupos ativos, ordenados por grupo, tipo e título.
     *
     * @return list<array<string, mixed>>
     */
    public function listarPublicos(string $busca, string $tipo): array
    {
        $condicoes = ['m.ativo = 1', 'g.ativo = 1'];
        $parametros = [];

        if ($busca !== '') {
            $like = '%' . addcslashes($busca, '%_\\') . '%';
            $condicoes[] = '(m.titulo LIKE :busca_titulo OR m.descricao LIKE :busca_descricao OR g.titulo LIKE :busca_grupo)';
            $parametros += ['busca_titulo' => $like, 'busca_descricao' => $like, 'busca_grupo' => $like];
        }

        if ($tipo !== '') {
            $condicoes[] = 'm.tipo = :tipo';
            $parametros['tipo'] = $tipo;
        }

        return $this->consultar(
            'SELECT ' . self::COLUNAS . '
             FROM materiais m
             JOIN material_grupos g ON g.id = m.grupo_id
             JOIN arquivos f ON f.id = m.arquivo_id
             WHERE ' . implode(' AND ', $condicoes) . '
             ORDER BY g.ordem, g.titulo, m.tipo, m.titulo',
            $parametros
        );
    }

    /** @return list<array<string, mixed>> */
    public function listarParaAdmin(): array
    {
        return $this->consultar(
            'SELECT ' . self::COLUNAS . '
             FROM materiais m
             JOIN material_grupos g ON g.id = m.grupo_id
             JOIN arquivos f ON f.id = m.arquivo_id
             ORDER BY g.ordem, g.titulo, m.tipo, m.titulo'
        );
    }

    /**
     * @param array{grupo_id: int, titulo: string, descricao: ?string, tipo: string, arquivo_id: int,
     *              publicado_em: string, ativo: int, autor_id: int} $dados
     */
    public function criar(array $dados): int
    {
        $this->executar(
            'INSERT INTO materiais (grupo_id, titulo, descricao, tipo, arquivo_id, publicado_em, ativo, autor_id)
             VALUES (:grupo_id, :titulo, :descricao, :tipo, :arquivo_id, :publicado_em, :ativo, :autor_id)',
            $dados
        );

        return $this->ultimoId();
    }

    /**
     * @param array{grupo_id: int, titulo: string, descricao: ?string, tipo: string, arquivo_id: int,
     *              publicado_em: string, ativo: int} $dados
     */
    public function atualizar(int $id, array $dados): void
    {
        $this->executar(
            'UPDATE materiais
             SET grupo_id = :grupo_id, titulo = :titulo, descricao = :descricao, tipo = :tipo,
                 arquivo_id = :arquivo_id, publicado_em = :publicado_em, ativo = :ativo
             WHERE id = :id',
            $dados + ['id' => $id]
        );
    }

    public function contarAtivos(): int
    {
        return (int) ($this->consultarUm('SELECT COUNT(*) AS total FROM materiais WHERE ativo = 1')['total'] ?? 0);
    }
}
