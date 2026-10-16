<?php

declare(strict_types=1);

namespace App\Helpers;

/** Utilidades de texto: remoção de acentos, sugestão de usuário e leitura de links do YouTube. */
final class Texto
{
    private const ACENTOS = [
        'á' => 'a', 'à' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a',
        'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
        'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
        'ó' => 'o', 'ò' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o',
        'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
        'ç' => 'c', 'ñ' => 'n',
    ];

    private const PARTICULAS = ['de', 'da', 'do', 'das', 'dos', 'e'];

    public static function semAcentos(string $texto): string
    {
        return strtr(mb_strtolower($texto), self::ACENTOS);
    }

    /**
     * Sugere o login no formato nome.sobrenome (primeiro e último nome, sem acentos).
     * Ex.: "João Victor Bastos Sena" => "joao.sena".
     */
    public static function sugerirUsuario(string $nomeCompleto): string
    {
        $palavras = [];

        foreach (preg_split('/\s+/', trim(self::semAcentos($nomeCompleto))) ?: [] as $palavra) {
            $palavra = preg_replace('/[^a-z0-9]/', '', $palavra) ?? '';

            if ($palavra !== '' && !in_array($palavra, self::PARTICULAS, true)) {
                $palavras[] = $palavra;
            }
        }

        if ($palavras === []) {
            return 'usuario';
        }

        $usuario = count($palavras) === 1 ? $palavras[0] : $palavras[0] . '.' . $palavras[count($palavras) - 1];

        return substr($usuario, 0, 55);
    }

    /** Extrai o id de um vídeo do YouTube (watch, youtu.be, embed ou shorts). Devolve null se inválido. */
    public static function youtubeId(string $url): ?string
    {
        $partes = parse_url(trim($url));

        if ($partes === false || !isset($partes['host'])) {
            return null;
        }

        $host = strtolower(preg_replace('/^www\./', '', $partes['host']) ?? '');
        $caminho = $partes['path'] ?? '';
        $id = null;

        if ($host === 'youtu.be') {
            $id = ltrim($caminho, '/');
        } elseif (in_array($host, ['youtube.com', 'm.youtube.com', 'youtube-nocookie.com'], true)) {
            if (preg_match('#^/(?:embed|shorts|live)/([^/?]+)#', $caminho, $m)) {
                $id = $m[1];
            } else {
                parse_str($partes['query'] ?? '', $consulta);
                $id = is_string($consulta['v'] ?? null) ? $consulta['v'] : null;
            }
        }

        return $id !== null && preg_match('/^[A-Za-z0-9_-]{11}$/', $id) === 1 ? $id : null;
    }
}
