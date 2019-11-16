#!/usr/local/bin/php
<?php
declare(strict_types=1);

require 'vendor/autoload.php';

(new \NunoMaduro\Collision\Provider)->register();

$dotenv = \Dotenv\Dotenv::create(__DIR__);
$dotenv->load();
$dotenv->required(['VAULT_URL', 'VAULT_TOKEN']);

if (empty($argv[1])) {
    throw new \HelmVaultInjector\Exception\Exception('Directory not provided');
}
$files = array_slice($argv, 1);

$log = new \Monolog\Logger('');
$log->pushHandler(new \Monolog\Handler\StreamHandler('php://stdout', \Monolog\Logger::DEBUG));

$secretProvider = new \HelmVaultInjector\SecretProvider(
    getenv('VAULT_URL'),
    getenv('VAULT_TOKEN'),
    $log
);

$fileIterator = new \HelmVaultInjector\FileIterator($files);
$templateProcessor = new \HelmVaultInjector\TemplateProcessor($secretProvider);
$processor = new \HelmVaultInjector\Processor($secretProvider, $templateProcessor, $log);

$processor->process($fileIterator);

$log->info('Done');
