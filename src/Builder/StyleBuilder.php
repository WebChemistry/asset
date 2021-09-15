<?php declare(strict_types = 1);

namespace WebChemistry\Asset\Builder;

use Nette\Utils\Html;

final class StyleBuilder
{

	private array $links = [];

	public function addLink(string $link): void
	{
		$this->links[] = $link;
	}

	public function build(): Html
	{
		$html = Html::el();

		foreach ($this->links as $link) {
			$html->insert(
				null,
				Html::el('link')->rel('stylesheet')->href($link)
			);
		}

		return $html;
	}

}
