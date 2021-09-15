<?php declare(strict_types = 1);

namespace WebChemistry\Asset\Builder;

use Nette\Utils\Html;
use WebChemistry\Asset\Builder\ValueObject\JavascriptLink;

final class JavascriptBuilder
{

	/** @var JavascriptLink[] */
	private array $links = [];

	public function addLink(string $link, bool $async = false, bool $defer = false): void
	{
		$this->links[] = new JavascriptLink($link, $async, $defer);
	}

	public function build(): Html
	{
		$html = Html::el();

		foreach ($this->links as $link) {
			$html->insert(
				null,
				Html::el('script')->src($link->getLink())->async($link->isAsync())->defer($link->isDefer())
			);
		}

		return $html;
	}

}
