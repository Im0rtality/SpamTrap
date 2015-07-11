<?php

namespace SymfonyBridge;

use Symfony\Component\Console\Application;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class CommandLoader
{
    public function loadCommands(Application $application)
    {
        $finder = new Finder();
        $finder->in(__DIR__ . '/../../src/SpamTrap')->name('*Command.php');
        /** @var SplFileInfo $file */
        foreach ($finder->files() as $file) {
            $class = 'SpamTrap\\' . str_replace(['.php', '/'], ['', '\\'], $file->getRelativePathname());
            $command = new $class();
            $application->add($command);
        }
    }
}
