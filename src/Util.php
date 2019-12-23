<?php

declare(strict_types=1);

namespace DOF\Testing;

use Throwable;
use Exception;
use Closure;
use ReflectionClass;
use FilesystemIterator;

class Util
{
    /**
     * Parse throwable and get a formatted result and compatible with DOF Exceptor
     *
     * @param Throwable $th
     * @param array $context: Throwable context
     */
    public static function parseThrowable(Throwable $th) : array
    {
        $data = [
            'code' => \method_exists($th, 'getNo')   ? $th->getNo()   : $th->getCode(),
            'info' => \method_exists($th, 'getInfo') ? $th->getInfo() : $th->getMessage(),
            'name' => \method_exists($th, 'getName') ? $th->getName() : Util::getObjectName($th),
            'file' => $th->getFile(),
            'line' => $th->getLine(),
            'call' => \explode(PHP_EOL, $th->getTraceAsString()),
        ];

        if (\method_exists($th, 'getContext')) {
            $data['more'] = $th->getContext();
        }

        if ($previous = $th->getPrevious()) {
            $data['last'] = Util::parseThrowable($previous);
        }

        return $data;
    }

    public static function getObjectName($object, bool $full = true) : ?string
    {
        if (! \is_object($object)) {
            return null;
        }

        $reflect = new ReflectionClass($object);

        return $full ? $reflect->getName() : $reflect->getShortName();
    }

    public static function walkDir(string $dir, Closure $callback)
    {
        if (! \is_dir($dir)) {
            throw new Exception("DIRECTORY_NOT_EXISTS: {$dir}");
        }

        $fsi = new FilesystemIterator($dir);
        foreach ($fsi as $path) {
            $callback($path);
        }

        unset($fsi);
    }

    public static function isClosure($target) : bool
    {
        return \is_object($target) && ($target instanceof Closure);
    }

    public static function gwt(array $dirs, array $excludes = [], bool $print = false)
    {
        list(list($start, $end, $low, $high), $success, $failure, $exception) = (new GWT)->walks($dirs, $excludes);

        $_success = \count($success);
        $_failure = \count($failure);
        $_exception = \count($exception);

        $result = [
            'time' => $end - $start,
            'memory' => $high - $low,
            'total' => $_success + $_failure + $_exception,
            'success' => $_success,
            'failure' => $failure,
            'exception' => $exception,
        ];
        
        if (! $print) {
            return $result;
        }

        echo \json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), PHP_EOL;
    }
}
