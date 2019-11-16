<?php
declare(strict_types=1);

namespace HelmVaultInjector;

class TemplateProcessor
{
    /**
     * @var SecretProvider
     */
    private $secretProvider;

    public function __construct(SecretProvider $secretProvider)
    {
        $this->secretProvider = $secretProvider;
    }

    public function process(string $content): string
    {
        return preg_replace_callback(
            '|%%(.*?)%%|',
            function (array $matches): string {
                var_dump($matches);
                return $this->replaceCallback($matches[1]);
            },
            $content
        );
    }

    private function replaceCallback(string $keypath): string
    {
        return $this->secretProvider->get($keypath);
    }
}