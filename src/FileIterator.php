<?php
declare(strict_types=1);

namespace HelmVaultInjector;

class FileIterator
{
    /**
     * @var string
     */
    private $files;

    /**
     * @param array|string[] $paths
     */
    public function __construct(array $files)
    {
        foreach ($files as $path) {
            if (!is_dir($path) && !is_file($path)) {
                throw new \HelmVaultInjector\Exception\Exception('File or directory not found:' . $path);
            }
        }
        $this->files = $files;
    }

    public function walk(): iterable
    {
        foreach ($this->files as $path) {
            if (is_file($path)) {
                yield new \SplFileInfo($path);
            } elseif (is_dir($path)) {
                $directory = new \RecursiveDirectoryIterator($this->files[0]);
                $iterator = new \RecursiveIteratorIterator($directory);
                /** @var \SplFileInfo $info */
                foreach ($iterator as $info) {
                    if (!$info->isFile()) {
                        continue;
                    }
                    yield $info;
                }
            } else {
                throw new \HelmVaultInjector\Exception\Exception('Unexpected path');
            }
        }
    }
}