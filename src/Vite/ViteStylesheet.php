<?php declare(strict_types = 1);

namespace WebChemistry\Asset\Vite;

final class ViteStylesheet implements ViteElement
{

	public function __construct(
		private string $url,
	)
	{
	}

	public function __toString(): string
	{
		return sprintf('<link rel="stylesheet" href="%s">', htmlspecialchars($this->url, ENT_QUOTES, 'UTF-8'));
	}

}
