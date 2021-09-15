<?php declare(strict_types = 1);

namespace WebChemistry\Asset\Builder;

use Nette\Http\IResponse;
use Symfony\Component\WebLink\HttpHeaderSerializer;
use Symfony\Component\WebLink\Link;

final class PreloadBuilder
{

	/** @var Link[] */
	private array $links = [];

	public function addStyleLink(string $link): void
	{
		$this->links[] = (new Link('preload', $link))->withAttribute('as', 'style');
	}
	public function addJavascriptLink(string $link): void
	{
		$this->links[] = (new Link('preload', $link))->withAttribute('as', 'script');
	}

	public function build(): ?string
	{
		$serializer = new HttpHeaderSerializer();

		return $serializer->serialize($this->links);
	}

	public function buildToResponse(IResponse $response): void
	{
		if ($build = $this->build()) {
			$response->addHeader('Link', $build);
		}
	}

}
