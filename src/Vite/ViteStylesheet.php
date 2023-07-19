<?php declare(strict_types = 1);

namespace WebChemistry\Asset\Vite;

final class ViteStylesheet implements ViteElement
{

	public function __construct(
		private string $url,
		private bool $dev,
	)
	{
	}

	public function __toString(): string
	{
		if ($this->dev) {
			return sprintf('<link rel="stylesheet" crossorigin="anonymous" href="%s">', htmlspecialchars($this->url, ENT_QUOTES, 'UTF-8'));
		}

		return sprintf('<link rel="stylesheet" href="%s">', htmlspecialchars($this->url, ENT_QUOTES, 'UTF-8'));
	}

}
