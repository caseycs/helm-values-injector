<?php
declare(strict_types=1);

require 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::create(__DIR__);
$dotenv->load();
$dotenv->required(['VAULT_URL', 'VAULT_TOKEN']);

if (empty($argv[1])) {
    echo 'Directory not provided' . PHP_EOL;
    exit(1);
}

if (!is_dir($argv[1])) {
    echo 'Directory invalid' . PHP_EOL;
    exit(1);
}

$secretProvider = new HelmVaultInjector\SecretProvider(getenv('VAULT_URL'), getenv('VAULT_TOKEN'));
$templateProcessor = new HelmVaultInjector\TemplateProcessor($secretProvider);

$fileWalker = new \HelmVaultInjector\FileWalker($argv[1]);
$processor = new \HelmVaultInjector\Processor($secretProvider, $templateProcessor);

try {
    echo 'Processing .yaml files' . PHP_EOL;
    $processor->processYaml($fileWalker);

    echo 'Processing .vault files' . PHP_EOL;
    $processor->processDotVault($fileWalker);
} catch (HelmVaultInjector\Exception\SecretNotFound $e) {
    echo $e->getMessage();
    exit(1);
} catch (HelmVaultInjector\Exception\SecretKeyMissing $e) {
    echo $e->getMessage();
    exit(1);
}

echo 'Done!' . PHP_EOL;
