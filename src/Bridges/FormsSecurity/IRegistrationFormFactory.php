<?php

declare(strict_types=1);

namespace Forms\Bridges\FormsSecurity;

interface IRegistrationFormFactory
{
	public function create(bool $confirmation = false, bool $emailAuthorization = false): RegistrationForm;
}
