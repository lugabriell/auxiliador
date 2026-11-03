<?php

declare(strict_types=1);

/**
 * Autoloader simples (PSR-4) para o namespace Auxiliador\.
 *
 * Auxiliador\Core\Env  ->  src/Core/Env.php
 *
 * Importante: o servidor (Linux) diferencia maiúsculas de minúsculas,
 * então o nome do arquivo deve ser idêntico ao nome da classe.
 */
spl_autoload_register(static function (string $class): void {
    $prefix = 'Auxiliador\\';

    // Ignora classes de outros namespaces
    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }

    $relative = substr($class, strlen($prefix));

    // Segurança: só aceita letras, números, underline e separador de namespace.
    // Isso impede nomes com "..", "/" ou caracteres que tentem sair da pasta src.
    if (!preg_match('/^[A-Za-z0-9_]+(\\\\[A-Za-z0-9_]+)*$/', $relative)) {
        return;
    }

    $file = __DIR__ . '/' . str_replace('\\', '/', $relative) . '.php';

    if (is_file($file)) {
        require $file;
    }
});