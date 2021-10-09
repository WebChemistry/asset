<?php declare(strict_types = 1);

namespace WebChemistry\Asset\Latte;

use Latte\Compiler;
use Latte\Macros\MacroSet;

final class StaticAssetMacro extends MacroSet
{

	public const LATTE_PROVIDER_NAME = 'staticAsset';

	public static function install(Compiler $compiler): void
	{
		$me = new static($compiler);

		$me->addMacro('staticAsset', 'echo $this->global->' . self::LATTE_PROVIDER_NAME . '->route(%node.args);');
	}
}
