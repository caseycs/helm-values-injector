<?php
declare(strict_types=1);

namespace HelmVaultInjector;

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

    public function __construct(SecretProvider $secretProvider, TemplateProcessor $templateProcessor)
    {
        $this->secretProvider = $secretProvider;
        $this->templateProcessor = $templateProcessor;
    }

    public function processYaml(FileWalker $fileWalker): void
    {
        /** @var \SplFileObject $file */
        foreach ($fileWalker->walk('/^.+\.yaml/i', 'r+') as $file) {
            $content = $file->fread($file->getSize());
            $newContent = $this->templateProcessor->process($content);

            $file->rewind();
            $file->fwrite($newContent);
            $file->ftruncate(strlen($newContent));
        }
    }

    public function processDotVault(FileWalker $fileWalker): void
    {
        /** @var \SplFileObject $file */
        foreach ($fileWalker->walk('/^.+\.vault/i', 'r') as $file) {
            $secretKey = trim($file->fread($file->getSize()));
            $secretContent = $this->secretProvider->get($secretKey);
            file_put_contents(substr($file->getPathname(), 0, -strlen('.vault')), $secretContent);
        }
    }
}