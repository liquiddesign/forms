<?php

declare(strict_types=1);

namespace Forms\Bridges\FormsSecurity;

interface IRegistrationFormFactory
{
	public function create(): RegistrationForm;
}
