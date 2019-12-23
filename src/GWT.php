<?php

declare(strict_types=1);

namespace DOF\Testing;

use Exception;
use Closure;

/**
 * Given-When-Then descriptive model
 */
final class GWT
{
    private $success = [];
    private $failure = [];
    private $exception = [];

    public function append(string $title, string $file, int $line, $result, bool $success = null)
    {
        if ($success) {
            $this->success[] = \compact('title', 'file', 'line');
        } elseif (\is_null($success)) {
            $this->exception[] = \compact('title', 'file', 'line', 'result');
        } else {
            $this->failure[] = \compact('title', 'file', 'line', 'result');
        }
    }

    public function execute($given, $when, $then, &$result) : bool
    {
        $tester = new Tester;

        // See: <https://stackoverflow.com/questions/7067536/how-to-call-a-closure-that-is-a-class-variable>
        $given = Util::isClosure($given) ? ($given)($tester) : $given;
        $result = Util::isClosure($when) ? ($when)($given, $tester) : $when;

        if (Util::isClosure($then)) {
            $then = $then($result, $tester);
            return $then === true;
        }

        return $then === $result;
    }

    /**
     * Run GWT test cases by directory and exclude for some files
     *
     * @param array $dirs: The directories stores tests cases
     * @param array $excludes: The realpath of files to exclude
     */
    public function walks(array $dirs, array $excludes = []) : array
    {
        $start = \microtime(true);
        $low = \memory_get_usage();

        foreach ($dirs as $dir) {
            $this->test($dir, $excludes);
        }

        $end = \microtime(true);
        $high = \memory_get_peak_usage();

        return [
            [$start, $end, $low, $high],
            $this->success,
            $this->failure,
            $this->exception,
        ];
    }

    public function walk(string $dir, array $excludes = [])
    {
        $start = \microtime(true);
        $low = \memory_get_usage();

        $this->test($dir, $excludes);

        $end = \microtime(true);
        $high = \memory_get_peak_usage();

        return [
            [$start, $end, $low, $high],
            $this->success,
            $this->failure,
            $this->exception,
        ];
    }

    public function test(string $dir, array $excludes = [])
    {
        if (! \is_dir($dir)) {
            throw new Exception("DIRECTORY_NOT_EXISTS: {$dir}");
        }

        Util::walkDir($dir, function ($path) use ($excludes) {
            $realpath = $path->getRealPath();
            foreach ($excludes as $exclude) {
                $exclude = \realpath($exclude);
                if (! $exclude) {
                    continue;
                }
                if ($realpath === $exclude) {
                    return;
                }
            }

            if ($path->isDir()) {
                $this->test($realpath, $excludes);
                return;
            }
            if ($path->isFile() && (\strtolower($path->getExtension()) === 'php')) {
                // Here we use anonymous function call to setup a entirely new testing context
                (function ($gwt) {
                    include_once $gwt->file;
                })(new TestCase($realpath, $this));
            }
        });
    }
}
