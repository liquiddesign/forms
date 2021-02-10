<?php

declare(strict_types=1);

namespace Forms;

if (false) {
	/** @deprecated use FormFactory */
	class Forms extends FormFactory
	{
	}
} elseif (!class_exists(Forms::class)) {
	\class_alias(FormFactory::class, Forms::class);
}