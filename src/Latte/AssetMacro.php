<?php declare(strict_types = 1);

namespace WebChemistry\Asset\Latte;

use Latte\Compiler;
use Latte\MacroNode;
use Latte\Macros\MacroSet;
use Latte\PhpWriter;
use WebChemistry\Asset\Exceptions\AssetMacroException;

final class AssetMacro extends MacroSet {

	private const ALLOWED_TAGS = ['link', 'script'];
	private const TAGS_MAPPING = [
		'link' => 'href',
		'script' => 'src',
	];

	public static function install(Compiler $compiler): void {
		$me = new static($compiler);

		$me->addMacro('asset', null, null, [$me, 'assetAttr']);
	}

	public function assetAttr(MacroNode $node, PhpWriter $writer): string {
		if (!$node->htmlNode) {
			throw new AssetMacroException('Unexpected state');
		}

		if (!in_array($tag = $node->htmlNode->name, self::ALLOWED_TAGS)) {
			throw new AssetMacroException(
				sprintf('n:asset is allowed only in %s, not in %s', implode(', ', self::ALLOWED_TAGS), $tag)
			);
		}

		$attr = self::TAGS_MAPPING[$tag];

		return $writer->write(
			'echo \' \' . %word . \'="\' . $this->global->assetPackages->getUrl(%node.args) . \'"\';', $attr
		);
	}

}
