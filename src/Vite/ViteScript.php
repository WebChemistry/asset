<?php declare(strict_types = 1);

namespace WebChemistry\Asset\Vite;

final class ViteScript implements ViteElement
{

	public function __construct(
		private string $url,
	)
	{
	}

	public function __toString(): string
	{
		return sprintf('<script type="module" src="%s"></script>', htmlspecialchars($this->url, ENT_QUOTES, 'UTF-8'));
	}

}
