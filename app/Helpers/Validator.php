<?php

declare(strict_types=1);

namespace App\Helpers;

use DateTimeImmutable;

/**
 * Validação no servidor. Os dados do navegador nunca são considerados confiáveis.
 * Em cada campo, apenas a primeira mensagem de erro é guardada.
 */
final class Validator
{
    public const SENHA_MINIMA = 8;
    public const SENHA_MAXIMA = 72; // limite do bcrypt, algoritmo usado por password_hash

    /** @var array<string, string> */
    private array $erros = [];

    public function __construct(private array $dados)
    {
    }

    public function valor(string $campo): string
    {
        $valor = $this->dados[$campo] ?? '';

        return is_string($valor) ? trim($valor) : '';
    }

    public function adicionarErro(string $campo, string $mensagem): self
    {
        $this->erros[$campo] ??= $mensagem;

        return $this;
    }

    public function obrigatorio(string $campo, string $rotulo): self
    {
        if ($this->valor($campo) === '') {
            $this->adicionarErro($campo, 'Informe ' . $rotulo . '.');
        }

        return $this;
    }

    public function tamanho(string $campo, string $rotulo, int $minimo, int $maximo): self
    {
        $valor = $this->valor($campo);

        if ($valor === '') {
            return $this; // a obrigatoriedade é verificada em obrigatorio()
        }

        $tamanho = mb_strlen($valor);

        if ($tamanho < $minimo || $tamanho > $maximo) {
            $this->adicionarErro($campo, ucfirst($rotulo) . ' deve ter entre ' . $minimo . ' e ' . $maximo . ' caracteres.');
        }

        return $this;
    }

    public function email(string $campo, string $rotulo = 'o e-mail'): self
    {
        $valor = $this->valor($campo);

        if ($valor !== '' && filter_var($valor, FILTER_VALIDATE_EMAIL) === false) {
            $this->adicionarErro($campo, 'Informe ' . $rotulo . ' em um formato válido.');
        }

        return $this;
    }

    /** Aceita somente endereços http ou https (bloqueia javascript:, data: e similares). */
    public function url(string $campo, string $rotulo): self
    {
        $valor = $this->valor($campo);

        if ($valor === '') {
            return $this;
        }

        $partes = parse_url($valor);
        $esquema = strtolower((string) ($partes['scheme'] ?? ''));
        $valida = filter_var($valor, FILTER_VALIDATE_URL) !== false
            && in_array($esquema, ['http', 'https'], true)
            && !empty($partes['host']);

        if (!$valida) {
            $this->adicionarErro($campo, 'Informe ' . $rotulo . ' começando com http:// ou https://.');
        }

        return $this;
    }

    /** @param list<string> $validas */
    public function opcao(string $campo, string $rotulo, array $validas): self
    {
        if (!in_array($this->valor($campo), $validas, true)) {
            $this->adicionarErro($campo, 'Selecione ' . $rotulo . ' válido.');
        }

        return $this;
    }

    public function inteiro(string $campo, string $rotulo, int $minimo, int $maximo): self
    {
        $valor = $this->valor($campo);

        if ($valor === '') {
            return $this;
        }

        if (!ctype_digit($valor) || (int) $valor < $minimo || (int) $valor > $maximo) {
            $this->adicionarErro($campo, ucfirst($rotulo) . ' deve ser um número entre ' . $minimo . ' e ' . $maximo . '.');
        }

        return $this;
    }

    /** Data no formato AAAA-MM-DD (campo type="date"). Vazio é aceito. */
    public function data(string $campo, string $rotulo): self
    {
        $valor = $this->valor($campo);

        if ($valor === '') {
            return $this;
        }

        $data = DateTimeImmutable::createFromFormat('!Y-m-d', $valor);

        if ($data === false || $data->format('Y-m-d') !== $valor) {
            $this->adicionarErro($campo, 'Informe ' . $rotulo . ' em um formato válido.');
        }

        return $this;
    }

    public function senha(string $campo): self
    {
        $erro = self::erroDeSenha((string) ($this->dados[$campo] ?? ''));

        if ($erro !== null) {
            $this->adicionarErro($campo, $erro);
        }

        return $this;
    }

    public function confirmacao(string $campo, string $campoConfirmacao): self
    {
        $original = (string) ($this->dados[$campo] ?? '');
        $confirmacao = (string) ($this->dados[$campoConfirmacao] ?? '');

        if (!hash_equals($original, $confirmacao)) {
            $this->adicionarErro($campoConfirmacao, 'A confirmação não confere com a nova senha.');
        }

        return $this;
    }

    /** Regra de senha: tamanho mínimo, ao menos uma letra e um número. Devolve a mensagem de erro ou null. */
    public static function erroDeSenha(string $senha): ?string
    {
        if (strlen($senha) < self::SENHA_MINIMA || strlen($senha) > self::SENHA_MAXIMA) {
            return 'A senha deve ter entre ' . self::SENHA_MINIMA . ' e ' . self::SENHA_MAXIMA . ' caracteres.';
        }

        if (!preg_match('/\p{L}/u', $senha) || !preg_match('/\d/', $senha)) {
            return 'A senha deve conter ao menos uma letra e um número.';
        }

        return null;
    }

    public function falhou(): bool
    {
        return $this->erros !== [];
    }

    /** @return array<string, string> */
    public function erros(): array
    {
        return $this->erros;
    }
}
