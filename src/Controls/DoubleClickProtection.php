<?php

declare(strict_types=1);

namespace Forms\Controls;

use Nette\Forms\Controls\CsrfProtection;

class DoubleClickProtection extends CsrfProtection
{
	public function validate(): void
	{
		parent::validate();
		
		$session = $this->session->getSection(CsrfProtection::class);
		unset($session->token);
	}
}
