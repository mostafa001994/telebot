<?php

class Router
{
    public static function dispatch(PDO $pdo, array $update)
    {
        if (isset($update['callback_query'])) {

            (new CallbackHandler(
                $pdo,
                $update
            ))->handle();

            return;
        }

        if (!isset($update['message'])) {
            return;
        }

        if (isset($update['message']['contact'])) {

            (new ContactHandler(
                $pdo,
                $update
            ))->handle();

            return;
        }

        $text = trim(
            $update['message']['text'] ?? ''
        );

        $routes =
            require __DIR__ .
            '/../config/routes.php';

        if (!isset($routes[$text])) {

            (new MessageHandler(
                $pdo,
                $update
            ))->handle();

            return;
        }

        $class = $routes[$text];

        (new $class(
            $pdo,
            $update
        ))->handle();
    }
}