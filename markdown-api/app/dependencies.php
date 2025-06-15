<?php

declare(strict_types=1);

use App\Application\Settings\SettingsInterface;
use DI\ContainerBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([

        // Logger definition
        LoggerInterface::class => function (ContainerInterface $c) {
            $settings = $c->get(SettingsInterface::class);
            $loggerSettings = $settings->get('logger');

            $logger = new Logger($loggerSettings['name']);
            $logger->pushProcessor(new UidProcessor());
            $logger->pushHandler(new StreamHandler($loggerSettings['path'], $loggerSettings['level']));

            return $logger;
        },

        // DB (PDO) definition
        'db' => function (ContainerInterface $c) {
            $settings = $c->get(SettingsInterface::class)->get('db');

            $dsn = sprintf(
                "%s:host=%s;dbname=%s;charset=%s",
                $settings['driver'],
                $settings['host'],
                $settings['database'],
                $settings['charset']
            );

            return new PDO(
                $dsn,
                $settings['username'],
                $settings['password'],
                $settings['flags']
            );
        },
    ]);
};
