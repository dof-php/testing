<?php

declare(strict_types=1);

namespace DOF\Testing;

use Throwable;
use Closure;

class Unit
{
    public $title;
    private $gwt;

    public function __construct(string $title, GWT $gwt)
    {
        $this->title = $title;
        $this->gwt = $gwt;
    }

    public function unit(string $title, Closure $testing)
    {
        $testing(new Unit($title, $this->gwt));
    }

    public function add($given, $when, $then)
    {
        $last = \debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)[0] ?? [];
        $file = $last['file'] ?? '?';
        $line = $last['line'] ?? -1;

        $success = false;
        $result  = null;
        try {
            $success = $this->gwt->execute($given, $when, $then, $result);
        } catch (Throwable $th) {
            $success = null;
            $result = Util::parseThrowable($th);
            $result['trace'] = \array_slice($result['trace'] ?? [], 0, 3);
        }

        $this->gwt->append($this->title, $file, $line, $result, $success);
    }

    public function true($testing)
    {
        $last = \debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)[0] ?? [];
        $file = $last['file'] ?? '?';
        $line = $last['line'] ?? -1;

        $success = false;
        $result  = null;
        try {
            $result = Util::isClosure($testing) ? ($testing)(new Tester) : $testing;

            $success = (true === $result);
        } catch (Throwable $th) {
            $success = null;
            $result = Util::parseThrowable($th);
            $result['trace'] = \array_slice($result['trace'] ?? [], 0, 3);
        }

        $this->gwt->append($this->title, $file, $line, $result, $success);
    }

    public function false($testing)
    {
        $last = \debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)[0] ?? [];
        $file = $last['file'] ?? '?';
        $line = $last['line'] ?? -1;

        $success = false;
        $result  = null;
        try {
            $result = Util::isClosure($testing) ? ($testing)(new Tester) : $testing;

            $success = (false === $result);
        } catch (Throwable $th) {
            $success = null;
            $result = Util::parseThrowable($th);
            $result['trace'] = \array_slice($result['trace'] ?? [], 0, 3);
        }

        $this->gwt->append($this->title, $file, $line, $result, $success);
    }

    public function null($testing)
    {
        $last = \debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)[0] ?? [];
        $file = $last['file'] ?? '?';
        $line = $last['line'] ?? -1;

        $success = false;
        $result  = null;
        try {
            $result = Util::isClosure($testing) ? ($testing)(new Tester) : $testing;

            $success = (null === $result);
        } catch (Throwable $th) {
            $success = null;
            $result = Util::parseThrowable($th);
            $result['trace'] = \array_slice($result['trace'] ?? [], 0, 3);
        }

        $this->gwt->append($this->title, $file, $line, $result, $success);
    }

    public function neq($testing, $value, bool $force = true)
    {
        $last = \debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)[0] ?? [];
        $file = $last['file'] ?? '?';
        $line = $last['line'] ?? -1;

        $success = false;
        $result  = null;
        try {
            $result = Util::isClosure($testing) ? ($testing)(new Tester) : $testing;

            $success = $force ? ($result !== $value) : ($result != $value);
        } catch (Throwable $th) {
            $success = null;
            $result = Util::parseThrowable($th);
            $result['trace'] = \array_slice($result['trace'] ?? [], 0, 3);
        }

        $this->gwt->append($this->title, $file, $line, $result, $success);
    }

    public function eq($testing, $value, bool $force = true)
    {
        $last = \debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)[0] ?? [];
        $file = $last['file'] ?? '?';
        $line = $last['line'] ?? -1;

        $success = false;
        $result  = null;
        try {
            $result = Util::isClosure($testing) ? ($testing)(new Tester) : $testing;

            $success = $force ? ($result === $value) : ($result == $value);
        } catch (Throwable $th) {
            $success = null;
            $result = Util::parseThrowable($th);
            $result['trace'] = \array_slice($result['trace'] ?? [], 0, 3);
        }

        $this->gwt->append($this->title, $file, $line, $result, $success);
    }

    // DOF PHP only
    public function err(Closure $testing, array $err)
    {
        $last = \debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)[0] ?? [];
        $file = $last['file'] ?? '?';
        $line = $last['line'] ?? -1;

        $success = false;
        $result  = null;
        try {
            $result = ($testing)(new Tester);
        } catch (Throwable $th) {
            $code = $err[0] ?? -1;
            if (($_code = $th->getCode()) === $code) {
                $success = true;
            } else {
                $_code = \is_null($_code) ? 'null' : $_code;
                $result = "Expecting err code `{$_code}`, given `{$code}`";
            }
        }

        $this->gwt->append($this->title, $file, $line, $result, $success);
    }

    // DOF PHP only
    public function exceptor(Closure $testing, string $exceptor = null, string $name = null)
    {
        $last = \debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)[0] ?? [];
        $file = $last['file'] ?? '?';
        $line = $last['line'] ?? -1;

        $success = false;
        $result  = null;
        try {
            $result = ($testing)(new Tester);
        } catch (Throwable $th) {
            if (\is_null($exceptor)) {
                $success =  true;
            } else {
                $success = ($exceptor === ($_exceptor = Util::getObjectName($th, true)));
                if ($success) {
                    if (! \is_null($name)) {
                        if (\method_exists($th, 'getName')) {
                            $_name = $th->getName();
                            if (\is_string($_name)) {
                                $success = (\strtolower($name) === \strtolower($_name));
                                if (! $success) {
                                    $result = "Expecting exceptor name `{$_name}`, given `{$name}`";
                                }
                            } else {
                                $result = 'Return value of `getName()` is not a string. ('.\gettype($_name).')';
                            }
                        } else {
                            $result = "Method `getName()` doesn't exits in current exceptor {$_exceptor}";
                        }
                    }
                } else {
                    $result = "Expecting exceptor `{$_exceptor}`, given `{$exceptor}`";
                }
            }
        }

        $this->gwt->append($this->title, $file, $line, $result, $success);
    }

    public function exception(Closure $testing, string $exception = null)
    {
        $last = \debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)[0] ?? [];
        $file = $last['file'] ?? '?';
        $line = $last['line'] ?? -1;

        $success = false;
        $result  = null;
        try {
            $result = ($testing)(new Tester);
        } catch (Throwable $th) {
            if (\is_null($exception)) {
                $success = true;
            } else {
                if ($exception === ($_exception = Util::getObjectName($th, true))) {
                    $success = true;
                } else {
                    $result = "Expecting exception `{$_exception}`, given `{$exception}`";
                }
            }
        }

        $this->gwt->append($this->title, $file, $line, $result, $success);
    }
}
