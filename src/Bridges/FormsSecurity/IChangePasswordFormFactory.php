<?php

declare(strict_types=1);

namespace Forms\Bridges\FormsSecurity;

interface IChangePasswordFormFactory
{
	public function create(): ChangePasswordForm;
}
