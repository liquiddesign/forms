<?php

declare(strict_types=1);

namespace Forms\Bridges\FormsSecurity;

interface ILostPasswordFormFactory
{
	public function create(?string $class = null): LostPasswordForm;
}
