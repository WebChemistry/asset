<?php declare(strict_types = 1);

namespace WebChemistry\Asset\Vite;

final class ViteScript implements ViteElement
{

	public function __construct(
		private string $url,
		private ?string $nonce = null,
	)
	{
	}

	public function __toString(): string
	{
		return sprintf(
			'<script %stype="module" src="%s"></script>', 
			$this->nonce ? sprintf(' nonce="%s"', $this->nonce) : '',
			htmlspecialchars($this->url, ENT_QUOTES, 'UTF-8'),
		);
	}

}
