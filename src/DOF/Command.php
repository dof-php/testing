<?php

declare(strict_types=1);

namespace DOF\Testing\DOF;

use DOF\DOF;
use DOF\DMN;
use DOF\Convention;
use DOF\Util\FS;
use DOF\Util\Str;
use DOF\Util\Format;
use DOF\Testing\Util;

// Command class prepared for `dof-php/cli` package
// TODO: not finished yet
class Command
{
    /**
     * @CMD(test)
     * @Desc(Run all domain tests)
     */
    public function test($console)
    {
        foreach (DMN::list() as $domain => $list) {
            $tests = FS::path($domain, 'tests');
            if (! \is_dir($tests)) {
                continue;
            }

            $console->title("---- Domain Root: {$domain} ----");
            $this->__test($console, $tests);
            $console->line();
        }
    }

    /**
     * @CMD(test.domain)
     * @Desc(Run domain tests)
     * @Argv(1){notes=The domain name to run test cases}
     */
    public function testDomain($console)
    {
        $domain = $console->first('domain');
        if (! $domain) {
            $console->fail('MissingDomainName', true);
        }

        $_domain = DMN::path($domain);
        if (! $_domain) {
            $console->exceptor('DomainNotFound', \compact('domain'));
        }

        $this->__test($console, FS::path($_domain, 'tests'));
    }

    /**
     * @CMD(test.dir)
     * @Desc(Run DOF GWT test cases by directory)
     * @Option(path){notes=The directory to run test cases}
     */
    public function testDir($console)
    {
        if (! $console->hasOption('path')) {
            $console->fail('MissingTestsPath', true);
        }

        $path = $console->getOption('path');
        if (! \is_dir($path)) {
            $path = DOF::path($path);
            if (! \is_dir($path)) {
                $console->exceptor('TestsPathNotExists', \compact('path'));
            }
        }

        $this->__test($console, $path);
    }

    private function __test($console, string $dir, array $excludes = [])
    {
        $result = DOF::gwt([$dir], $excludes);
        if (Str::eq($console->getOption('output', ''), 'json', true)) {
            $console->info($result);
            return;
        }

        \extract($result);

        $console->info('-- Time Taken: '.($time).' s');
        $console->info('-- Memory Used: '.Format::bytes($memory));
        $console->info('-- Total Test Cases: '.$total);
        $console->success('-- Passed Tests: '.$success);
        $console->fail('-- Failed Tests: '.\count($failure));
        if ($_failure > 0) {
            $console->fail($failure);
        }
        $console->warn('-- Exception Tests: '.\count($exception));
        if ($_exception > 0) {
            $console->warn($exception);
        }
    }

    /**
    * @CMD(test.class)
    * @Desc(Testing all classes in dof project and detecting problems like uninjectable dependencies, unimported namesapces, undefined methods, etc.)
    */
    public function testClasses($console)
    {
        FS::walkr(DOF::path(Convention::DIR_DOMAIN), function ($path) {
            if (! Str::eq($path->getExtension(), 'php', true)) {
                return;
            }
            $file = $path->getRealpath();
            $class = Reflect::getFileNamespace($file, true);
            if (false === $class) {
                return;
            }

            // TODO
        });
    }

    /**
     * @CMD(test.framework)
     * @Desc(Run framework tests)
     * @Option(format){notes=Framework tests execute result format: console/json&default=console}
     */
    public function testFramework($console)
    {
        $tests = FS::path(DOF::root(false), ['gwt']);

        $console->info(Util::gwt([$tests], [
            FS::path($tests, '_data'),
        ], false));
    }
}
