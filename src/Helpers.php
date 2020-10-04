<?php

declare(strict_types=1);

namespace Forms;

class Helpers
{
	public static function reflectionOf(callable $callable): \ReflectionFunctionAbstract
	{
		if ($callable instanceof \Closure) {
			return new \ReflectionFunction($callable);
		}

		if (\is_string($callable)) {
			$pcs = \explode('::', $callable);

			return \count($pcs) > 1 ? new \ReflectionMethod($pcs[0], $pcs[1]) : new \ReflectionFunction($callable);
		}

		if (!\is_array($callable)) {
			$callable = [$callable, '__invoke'];
		}

		return new \ReflectionMethod($callable[0], $callable[1]);
	}
}
