<?php

declare(strict_types=1);

namespace Forms;

class Container extends \Nette\Forms\Container
{
	use LocaleComponentsTrait;
	use ComponentsTrait;

	public function addContainer(int|string $name): Container
	{
		$control = new static();
		$control->currentGroup = $this->currentGroup;
		$this->currentGroup?->add($control);

		return $this[$name] = $control;
	}
}
