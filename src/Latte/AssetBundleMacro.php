<?php declare(strict_types = 1);

namespace WebChemistry\Asset\Latte;

use Latte\Compiler;
use Latte\Macros\MacroSet;

final class AssetBundleMacro extends MacroSet {

	public const LATTE_PROVIDER_NAME = 'assetBundleManager';

	public static function install(Compiler $compiler): void {
		$me = new static($compiler);

		$me->addMacro('assetBundle', 'echo $this->global->' . self::LATTE_PROVIDER_NAME . '->build(%node.args);');
	}

}
