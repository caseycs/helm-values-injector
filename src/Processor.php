<?php
declare(strict_types=1);

namespace HelmVaultInjector;

use Psr\Log\LoggerInterface;

class Processor
{
    /**
     * @var SecretProvider
     */
    private $secretProvider;

    /**
     * @var TemplateProcessor
     */
    private $templateProcessor;
    /**
     * @var LoggerInterface
     */
    private $log;


    public function __construct(SecretProvider $secretProvider, TemplateProcessor $templateProcessor, LoggerInterface $log)
    {
        $this->secretProvider = $secretProvider;
        $this->templateProcessor = $templateProcessor;
        $this->log = $log;
    }

    public function process(FileIterator $fileWalker): void
    {
        /** @var \SplFileInfo $file */
        foreach ($fileWalker->walk() as $file) {
            if (preg_match('~\.yaml$~', $file->getBasename())) {
                $this->log->info('Updating: ' . $file->getPathname());

                $fileO = new \SplFileObject($file->getPathname(), 'r+');

                $content = $fileO->fread($file->getSize());
                $newContent = $this->templateProcessor->process($content);

                if ($newContent !== $content) {
                    $fileO->rewind();
                    $fileO->fwrite($newContent);
                    $fileO->ftruncate(strlen($newContent));
                }
            } elseif (preg_match('~\.vault$~', $file->getBasename())) {
                $fileO = new \SplFileObject($file->getPathname(), 'r');

                $secretKey = trim($fileO->fread($fileO->getSize()));
                $secretContent = $this->secretProvider->get($secretKey);

                $newFilename = substr($fileO->getPathname(), 0, -strlen('.vault'));
                file_put_contents($newFilename, $secretContent);

                $this->log->info('Added: ' . $newFilename);
            } elseif (preg_match('~\.vault.base64$~', $file->getBasename())) {
                $fileO = new \SplFileObject($file->getPathname(), 'r');

                $secretKey = trim($fileO->fread($fileO->getSize()));
                $secretContent = $this->secretProvider->get($secretKey);
                $secretContentDecoded = base64_decode($secretContent);

                $newFilename = substr($fileO->getPathname(), 0, -strlen('.vault.base64'));
                file_put_contents($newFilename, $secretContentDecoded);
                $this->log->info('Added: ' . $newFilename);
            }
        }
    }
}