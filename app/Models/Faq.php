<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use Throwable;

/** Perguntas frequentes, com vínculos opcionais para vídeos, materiais, legislações e contatos. */
final class Faq extends Model
{
    /** Tipos de vínculo aceitos (valores gravados em faq_vinculos.tipo). */
    public const TIPOS_VINCULO = ['video', 'material', 'legislacao', 'contato'];

    private const COLUNAS = 'f.id, f.categoria, f.pergunta, f.resposta, f.ordem, f.ativo, f.criado_em';

    /** @return array<string, mixed>|null */
    public function buscarPorId(int $id): ?array
    {
        return $this->consultarUm('SELECT ' . self::COLUNAS . ' FROM faqs f WHERE f.id = :id LIMIT 1', ['id' => $id]);
    }

    /**
     * Perguntas ativas, com busca por texto (pergunta e resposta) e filtro de categoria.
     *
     * @return list<array<string, mixed>>
     */
    public function listarPublicas(string $busca, string $categoria): array
    {
        $condicoes = ['f.ativo = 1'];
        $parametros = [];

        if ($busca !== '') {
            $like = '%' . addcslashes($busca, '%_\\') . '%';
            $condicoes[] = '(f.pergunta LIKE :busca_pergunta OR f.resposta LIKE :busca_resposta)';
            $parametros += ['busca_pergunta' => $like, 'busca_resposta' => $like];
        }

        if ($categoria !== '') {
            $condicoes[] = 'f.categoria = :categoria';
            $parametros['categoria'] = $categoria;
        }

        return $this->consultar(
            'SELECT ' . self::COLUNAS . ' FROM faqs f WHERE ' . implode(' AND ', $condicoes) . '
             ORDER BY f.categoria, f.ordem, f.id
             LIMIT 300',
            $parametros
        );
    }

    /** @return list<array<string, mixed>> */
    public function listarParaAdmin(): array
    {
        return $this->consultar('SELECT ' . self::COLUNAS . ' FROM faqs f ORDER BY f.categoria, f.ordem, f.id');
    }

    public function proximaOrdem(string $categoria): int
    {
        $linha = $this->consultarUm(
            'SELECT COALESCE(MAX(ordem), 0) + 1 AS proxima FROM faqs WHERE categoria = :categoria',
            ['categoria' => $categoria]
        );

        return (int) ($linha['proxima'] ?? 1);
    }

    /** @param array{categoria: string, pergunta: string, resposta: string, ordem: int, ativo: int, autor_id: int} $dados */
    public function criar(array $dados): int
    {
        $this->executar(
            'INSERT INTO faqs (categoria, pergunta, resposta, ordem, ativo, autor_id)
             VALUES (:categoria, :pergunta, :resposta, :ordem, :ativo, :autor_id)',
            $dados
        );

        return $this->ultimoId();
    }

    /** @param array{categoria: string, pergunta: string, resposta: string, ordem: int, ativo: int} $dados */
    public function atualizar(int $id, array $dados): void
    {
        $this->executar(
            'UPDATE faqs SET categoria = :categoria, pergunta = :pergunta, resposta = :resposta,
                             ordem = :ordem, ativo = :ativo
             WHERE id = :id',
            $dados + ['id' => $id]
        );
    }

    /**
     * Ids vinculados a uma pergunta, agrupados por tipo (usado no formulário de edição).
     *
     * @return array<string, list<int>>
     */
    public function vinculosIds(int $faqId): array
    {
        $resultado = array_fill_keys(self::TIPOS_VINCULO, []);

        foreach ($this->consultar('SELECT tipo, referencia_id FROM faq_vinculos WHERE faq_id = :faq_id', ['faq_id' => $faqId]) as $linha) {
            if (isset($resultado[$linha['tipo']])) {
                $resultado[$linha['tipo']][] = (int) $linha['referencia_id'];
            }
        }

        return $resultado;
    }

    /** Substitui todos os vínculos da pergunta de uma vez (tudo ou nada). */
    public function salvarVinculos(int $faqId, array $vinculos): void
    {
        $this->conexao->beginTransaction();

        try {
            $this->executar('DELETE FROM faq_vinculos WHERE faq_id = :faq_id', ['faq_id' => $faqId]);

            foreach ($vinculos as $tipo => $ids) {
                if (!in_array($tipo, self::TIPOS_VINCULO, true)) {
                    continue;
                }

                foreach ($ids as $referenciaId) {
                    $this->executar(
                        'INSERT IGNORE INTO faq_vinculos (faq_id, tipo, referencia_id) VALUES (:faq_id, :tipo, :referencia_id)',
                        ['faq_id' => $faqId, 'tipo' => $tipo, 'referencia_id' => (int) $referenciaId]
                    );
                }
            }

            $this->conexao->commit();
        } catch (Throwable $e) {
            $this->conexao->rollBack();

            throw $e;
        }
    }

    /**
     * Vínculos das perguntas informadas, já com o título do conteúdo. Conteúdos ocultados ficam de fora.
     * Uma só consulta para todas as perguntas da página.
     *
     * @param list<int> $faqIds
     * @return array<int, list<array<string, mixed>>> vínculos indexados pelo id da pergunta
     */
    public function vinculosDetalhados(array $faqIds): array
    {
        if ($faqIds === []) {
            return [];
        }

        $marcas = [];
        $parametros = [];

        foreach (array_values($faqIds) as $posicao => $id) {
            $marcas[] = ':faq' . $posicao;
            $parametros['faq' . $posicao] = (int) $id;
        }

        $linhas = $this->consultar(
            "SELECT t.faq_id, t.tipo, t.referencia_id, t.titulo, t.legislacao_url, t.legislacao_arquivo
             FROM (
                 SELECT fv.faq_id, fv.tipo, fv.referencia_id,
                        CASE fv.tipo
                            WHEN 'video' THEN v.titulo
                            WHEN 'material' THEN m.titulo
                            WHEN 'legislacao' THEN l.titulo
                            ELSE c.nome
                        END AS titulo,
                        l.url AS legislacao_url, l.arquivo_id AS legislacao_arquivo
                 FROM faq_vinculos fv
                 LEFT JOIN videos v ON fv.tipo = 'video' AND v.id = fv.referencia_id AND v.ativo = 1
                 LEFT JOIN materiais m ON fv.tipo = 'material' AND m.id = fv.referencia_id AND m.ativo = 1
                 LEFT JOIN legislacoes l ON fv.tipo = 'legislacao' AND l.id = fv.referencia_id AND l.ativo = 1
                 LEFT JOIN contatos c ON fv.tipo = 'contato' AND c.id = fv.referencia_id
                 WHERE fv.faq_id IN (" . implode(',', $marcas) . ')
             ) t
             WHERE t.titulo IS NOT NULL
             ORDER BY t.faq_id, t.tipo, t.titulo',
            $parametros
        );

        $agrupado = [];

        foreach ($linhas as $linha) {
            $agrupado[(int) $linha['faq_id']][] = $linha;
        }

        return $agrupado;
    }
}
