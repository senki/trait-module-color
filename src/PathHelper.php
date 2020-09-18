<?php

declare(strict_types=1);

namespace App;

use Exception;
use Webmozart\PathUtil\Path;

class PathHelper
{
    public function getCwd(): string
    {
        $cwd = getcwd();
        if ($cwd === false) {
            throw new Exception("Can't access the current directory");
        }
        return $cwd;
    }

    public function makeAbsolute(string $target): string
    {
        return Path::makeAbsolute($target, $this->getCwd());
    }

    public function getFilename(string $target): string
    {
        return Path::getFilename($target);
    }

    public function isDir(string $target): bool
    {
        return is_dir($target);
    }

    public function collectFromDir(string $target): array
    {
        if (!$this->isDir($target)) {
            throw new Exception("Not a directory");
        }
        $buffer = [];
        foreach (scandir($target) as $item) {
            if (in_array($item, ['.', '..'])) {
                continue;
            }
            $buffer[] = $target . DIRECTORY_SEPARATOR . $item;
        }
        return $buffer;
    }
}
