<?php

declare(strict_types=1);

namespace Auxiliador\Core;

use RuntimeException;

/**
 * Carrega variáveis do arquivo .env e permite lê-las com tipos seguros.
 * Os valores ficam só em memória: não usamos putenv() nem $_ENV,
 * para não vazar segredos em phpinfo(), logs ou processos filhos.
 */
final class Env
{
    /** @var array<string, string> */
    private static array $vars = [];

    public static function load(string $path): void
    {
        if (!is_file($path) || !is_readable($path)) {
            // Mensagem genérica: não revela o caminho do servidor.
            throw new RuntimeException('Arquivo de configuração (.env) não encontrado.');
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            throw new RuntimeException('Não foi possível ler o arquivo de configuração.');
        }

        foreach ($lines as $line) {
            $line = trim($line);

            // Ignora comentários e linhas sem "="
            if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            // Só aceita nomes no formato MAIUSCULAS_COM_UNDERLINE
            if (!preg_match('/^[A-Z][A-Z0-9_]*$/', $key)) {
                continue;
            }

            // Remove aspas simples ou duplas ao redor do valor
            if (strlen($value) >= 2) {
                $first = $value[0];
                $last = $value[strlen($value) - 1];
                if (($first === '"' || $first === "'") && $first === $last) {
                    $value = substr($value, 1, -1);
                }
            }

            self::$vars[$key] = $value;
        }
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        return self::$vars[$key] ?? $default;
    }

    /** Lê uma variável obrigatória; falha se estiver ausente ou vazia. */
    public static function require(string $key): string
    {
        $value = self::$vars[$key] ?? '';
        if ($value === '') {
            throw new RuntimeException("Variável de configuração obrigatória ausente: {$key}");
        }
        return $value;
    }

    public static function bool(string $key, bool $default = false): bool
    {
        if (!isset(self::$vars[$key])) {
            return $default;
        }
        return in_array(strtolower(self::$vars[$key]), ['1', 'true', 'yes', 'on'], true);
    }

    public static function isProduction(): bool
    {
        return self::get('APP_ENV', 'production') === 'production';
    }
}