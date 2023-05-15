<?php declare(strict_types = 1);

namespace WebChemistry\Asset\Latte\Extension\Node;

use Generator;
use Latte\Compiler\Nodes\AreaNode;
use Latte\Compiler\Nodes\Html\ElementNode;
use Latte\Compiler\Nodes\StatementNode;
use Latte\Compiler\PrintContext;
use Latte\Compiler\Tag;

final class IfViteNode extends StatementNode
{

	public ?ElementNode $element;

	public AreaNode $content;

	public ViteNode $parent;

	public static function create(Tag $tag): Generator
	{
		$parent = ViteNode::create($tag);

		if (!$tag->isNAttribute()) {
			return $parent;
		}

		$node = new self();
		$node->element = $tag->isNAttribute() ? $tag->htmlElement : null;
		$node->parent = $parent;

		$tag->replaceNAttribute($parent);

		[$node->content] = yield;

		return $node;
	}

	public function print(PrintContext $context): string
	{
		$condition = $this->parent->getEnvironmentCondition($context, false);
		$tagName = $this->element?->name;

		if ($condition !== null && $tagName === 'link') {
			return $context->format('%raw { %node }', $condition, $this->content);
		}

		return $context->format('%node', $this->content);
	}

}
