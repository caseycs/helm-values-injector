<?php
declare(strict_types=1);

namespace HelmVaultInjector;

class TemplateProcessor
{
    /**
     * @var SecretProvider
     */
    private $secretProvider;

    private const BRACKET_OPEN = '%%';
    private const BRACKET_CLOSE = '%%';

    public function __construct(SecretProvider $secretProvider)
    {
        $this->secretProvider = $secretProvider;
    }

    public function process(string $content): string
    {
        return preg_replace_callback(
            sprintf('|%s(.*?)%s|',
                preg_quote(self::BRACKET_OPEN, '|'),
                preg_quote(self::BRACKET_CLOSE, '|')
            ),
            function (array $matches): string {
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