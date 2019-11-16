<?php
declare(strict_types=1);

namespace HelmVaultInjector;

class FileWalker
{
    /**
     * @var string
     */
    private $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function walk(string $filenamePattern, string $openMode): iterable
    {
        $directory = new \RecursiveDirectoryIterator($this->path);
        $iterator = new \RecursiveIteratorIterator($directory);
        $regex = new \RegexIterator($iterator, $filenamePattern, \RecursiveRegexIterator::GET_MATCH);

        foreach ($regex as $info) {
            yield new \SplFileObject($info[0], $openMode);
        }
    }
}