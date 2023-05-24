<?php declare(strict_types = 1);

namespace WebChemistry\Asset\Latte\Extension\Node;

use Latte\Compiler\Nodes\StatementNode;
use Latte\Compiler\PrintContext;
use Latte\Compiler\Tag;

final class RenderViteNode extends StatementNode
{

	public static function create(Tag $tag): self
	{
		return new self();
	}

	public function print(PrintContext $context): string
	{
		return $context->format('echo $this->global->vitePackage->renderToString();');
	}

}
