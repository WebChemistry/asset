<?php declare(strict_types = 1);

namespace WebChemistry\Asset\Latte\Extension\Node;

use Generator;
use Latte\Compiler\Nodes\Html\ElementNode;
use Latte\Compiler\Nodes\Php\Expression\ArrayNode;
use Latte\Compiler\Nodes\Php\ExpressionNode;
use Latte\Compiler\Nodes\StatementNode;
use Latte\Compiler\PrintContext;
use Latte\Compiler\Tag;

final class AssetNode extends StatementNode
{

	private ExpressionNode $file;

	private ArrayNode $arguments;

	private ?ElementNode $element;

	public static function create(Tag $tag): self
	{
		$tag->expectArguments();

		$node = new self();
		$node->element = $tag->isNAttribute() ? $tag->htmlElement : null;
		$node->file = $tag->parser->parseUnquotedStringOrExpression();
		$tag->parser->stream->tryConsume(',');
		$node->arguments = $tag->parser->parseArguments();

		return $node;
	}

	public function print(PrintContext $context): string
	{
		$code = $context->format(
			'$this->global->packages->getUrl(%node, %args?)',
			$this->file,
			$this->arguments,
		);

		if ($this->element) {
			$attribute = match ($this->element->name) {
				'img', 'script' => 'src',
				default => 'href',
			};

			$code = sprintf('echo \' %s="\' . %s . \'"\';', $attribute, $code);
		} else {
			$code = sprintf('echo %s;', $code);
		}

		return $code;
	}

	public function &getIterator(): Generator
	{
		yield $this->file;
		yield $this->arguments;
	}

}
