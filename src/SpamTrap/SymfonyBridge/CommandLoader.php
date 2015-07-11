<?php

namespace SpamTrap\SymfonyBridge;

use Symfony\Component\Console\Application;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class CommandLoader
{
    public function loadCommands(Application $application)
    {
        $finder = new Finder();
        $finder->in(__DIR__ . '/../../../src/')->name('*Command.php');
        /** @var SplFileInfo $file */
        foreach ($finder->files() as $file) {
            $class = str_replace(['.php', '/'], ['', '\\'], $file->getRelativePathname());
            $command = new $class();
            $application->add($command);
        }
    }
}
