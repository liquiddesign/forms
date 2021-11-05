<?php

declare(strict_types=1);

namespace Forms\Bridges\FormsSecurity;

use Nette\Security\User;

trait SecurityFormTrait
{
	protected User $user;

	public function setUser(User $user): void
	{
		$this->user = $user;
	}
}
