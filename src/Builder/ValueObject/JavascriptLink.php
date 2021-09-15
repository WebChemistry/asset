<?php declare(strict_types = 1);

namespace WebChemistry\Asset\Builder\ValueObject;

final class JavascriptLink
{

	private string $link;

	private bool $defer;

	private bool $async;

	public function __construct(string $link, bool $async = false, bool $defer = false)
	{
		$this->link = $link;
		$this->async = $async;
		$this->defer = $defer;
	}

	public function getLink(): string
	{
		return $this->link;
	}

	public function isDefer(): bool
	{
		return $this->defer;
	}

	public function isAsync(): bool
	{
		return $this->async;
	}

}
