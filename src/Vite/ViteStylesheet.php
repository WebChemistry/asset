<?php declare(strict_types = 1);

namespace WebChemistry\Asset\Vite;

final class ViteStylesheet implements ViteElement
{

	public function __construct(
		private string $url,
		private bool $dev,
		private ?string $nonce = null,
	)
	{
	}

	public function __toString(): string
	{
		if ($this->dev) {
			return sprintf(
				'<link %srel="stylesheet" crossorigin="anonymous" href="%s">',
				$this->nonce ? sprintf(' nonce="%s"', $this->nonce) : '',
				htmlspecialchars($this->url, ENT_QUOTES, 'UTF-8'),
			);
		}

		return sprintf(
			'<link %srel="stylesheet" href="%s">',
			$this->nonce ? sprintf(' nonce="%s"', $this->nonce) : '',
			htmlspecialchars($this->url, ENT_QUOTES, 'UTF-8'),
		);
	}

}
