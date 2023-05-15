<?php declare(strict_types = 1);

namespace WebChemistry\Asset\Latte\Extension\Node;

use Generator;
use Latte\Compiler\Nodes\Html\ElementNode;
use Latte\Compiler\Nodes\Php\Expression\ArrayNode;
use Latte\Compiler\Nodes\Php\ExpressionNode;
use Latte\Compiler\Nodes\StatementNode;
use Latte\Compiler\PrintContext;
use Latte\Compiler\Tag;

final class ViteNode extends StatementNode
{

	public ExpressionNode $file;

	public ArrayNode $arguments;

	public ?ElementNode $element;

	public bool $capture = false;

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

			if ($this->element->name === 'script') {
				$code .= sprintf($context->format("\nif (\$this->global->packages->getVersion('!env', %args?) === 'dev') {\n", $this->arguments));
				$code .= sprintf("\techo ' type=\"module\"';\n");
				$code .= sprintf("}\n");
			}
		} else {
			$code = sprintf('echo %s;', $code);
		}

		return $code;
	}

	public function getEnvironmentCondition(PrintContext $context, bool $dev = true): ?string
	{
		$element = $this->element;

		if (!$element) {
			return null;
		}

		$condition = $dev ? '===' : '!==';

		return $context->format('if ($this->global->packages->getVersion("!env", %args?) %raw "dev")', $this->arguments, $condition);
	}

	public function &getIterator(): Generator
	{
		yield $this->file;
		yield $this->arguments;
	}

}
