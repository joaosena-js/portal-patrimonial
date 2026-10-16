<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/** Legislação e normativos aplicados à gestão patrimonial. */
final class Legislacao extends Model
{
    private const COLUNAS = 'l.id, l.categoria, l.numero, l.titulo, l.ementa, l.data_publicacao, l.situacao,
                             l.url, l.arquivo_id, l.ativo, l.criado_em, f.extensao AS arquivo_extensao,
                             f.tamanho AS arquivo_tamanho';

    /** @return array<string, mixed>|null */
    public function buscarPorId(int $id): ?array
    {
        return $this->consultarUm(
            'SELECT ' . self::COLUNAS . '
             FROM legislacoes l LEFT JOIN arquivos f ON f.id = l.arquivo_id
             WHERE l.id = :id LIMIT 1',
            ['id' => $id]
        );
    }

    /**
     * Documentos ativos, com pesquisa textual (título, número e "do que trata") e filtro de categoria.
     *
     * @return list<array<string, mixed>>
     */
    public function listarPublicos(string $busca, string $categoria): array
    {
        $condicoes = ['l.ativo = 1'];
        $parametros = [];

        if ($busca !== '') {
            $like = '%' . addcslashes($busca, '%_\\') . '%';
            $condicoes[] = '(l.titulo LIKE :busca_titulo OR l.numero LIKE :busca_numero OR l.ementa LIKE :busca_ementa)';
            $parametros += ['busca_titulo' => $like, 'busca_numero' => $like, 'busca_ementa' => $like];
        }

        if ($categoria !== '') {
            $condicoes[] = 'l.categoria = :categoria';
            $parametros['categoria'] = $categoria;
        }

        return $this->consultar(
            'SELECT ' . self::COLUNAS . '
             FROM legislacoes l LEFT JOIN arquivos f ON f.id = l.arquivo_id
             WHERE ' . implode(' AND ', $condicoes) . '
             ORDER BY l.data_publicacao DESC, l.titulo
             LIMIT 200',
            $parametros
        );
    }

    /** @return list<array<string, mixed>> */
    public function listarParaAdmin(): array
    {
        return $this->consultar(
            'SELECT ' . self::COLUNAS . '
             FROM legislacoes l LEFT JOIN arquivos f ON f.id = l.arquivo_id
             ORDER BY l.categoria, l.data_publicacao DESC, l.titulo'
        );
    }

    /**
     * @param array{categoria: string, numero: ?string, titulo: string, ementa: ?string, data_publicacao: ?string,
     *              situacao: string, url: ?string, arquivo_id: ?int, ativo: int, autor_id: int} $dados
     */
    public function criar(array $dados): int
    {
        $this->executar(
            'INSERT INTO legislacoes
                (categoria, numero, titulo, ementa, data_publicacao, situacao, url, arquivo_id, ativo, autor_id)
             VALUES
                (:categoria, :numero, :titulo, :ementa, :data_publicacao, :situacao, :url, :arquivo_id, :ativo, :autor_id)',
            $dados
        );

        return $this->ultimoId();
    }

    /**
     * @param array{categoria: string, numero: ?string, titulo: string, ementa: ?string, data_publicacao: ?string,
     *              situacao: string, url: ?string, arquivo_id: ?int, ativo: int} $dados
     */
    public function atualizar(int $id, array $dados): void
    {
        $this->executar(
            'UPDATE legislacoes
             SET categoria = :categoria, numero = :numero, titulo = :titulo, ementa = :ementa,
                 data_publicacao = :data_publicacao, situacao = :situacao, url = :url,
                 arquivo_id = :arquivo_id, ativo = :ativo
             WHERE id = :id',
            $dados + ['id' => $id]
        );
    }
}
