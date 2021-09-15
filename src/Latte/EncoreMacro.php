<?php declare(strict_types = 1);

namespace WebChemistry\Asset\Latte;

use Latte\Compiler;
use Latte\Macros\MacroSet;

final class EncoreMacro extends MacroSet
{

	public const LATTE_PROVIDER_NAME = 'encore';

	public static function install(Compiler $compiler): void
	{
		$me = new static($compiler);

		$me->addMacro('encore', 'echo $this->global->' . self::LATTE_PROVIDER_NAME . '->build(%node.args);');
	}

}
