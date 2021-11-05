<?php

declare(strict_types=1);

namespace Forms\Bridges\FormsSecurity;

interface ILoginFormFactory
{
	public function create(array $classes): LoginForm;
}
