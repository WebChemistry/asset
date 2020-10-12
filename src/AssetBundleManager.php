<?php declare(strict_types = 1);

namespace WebChemistry\Asset;

use Nette\Http\IResponse;
use Nette\SmartObject;
use Symfony\Component\Asset\Packages;
use Symfony\Component\WebLink\HttpHeaderSerializer;
use Symfony\Component\WebLink\Link;
use WebChemistry\Asset\Exceptions\AssetBundleException;

final class AssetBundleManager
{

	use SmartObject;

	/** @var AssetBundle[] */
	private array $bundles;

	private Packages $packages;

	private IResponse $response;

	/**
	 * @param AssetBundle[] $bundles
	 */
	public function __construct(array $bundles, Packages $packages, IResponse $response)
	{
		foreach ($bundles as $bundle) {
			$this->addBundle($bundle);
		}

		$this->packages = $packages;
		$this->response = $response;
	}

	public function addBundle(AssetBundle $bundle): self
	{
		$this->bundles[$bundle->getName()] = $bundle;

		return $this;
	}

	protected function assertBundleExists(string $bundle): void
	{
		if (!isset($this->bundles[$bundle])) {
			throw new AssetBundleException(sprintf('Asset bundle %s not exists', $bundle));
		}
	}

	public function preload(string $bundle): void
	{
		$this->assertBundleExists($bundle);

		$links = [];
		foreach ($this->bundles[$bundle]->getEntries() as $entry) {
			$url = $this->packages->getUrl($entry->getPath(), $entry->getPackageName());

			$link = new Link('preload', $url);
			switch ($entry->getType()) {
				case 'css':
					$link = $link->withAttribute('as', 'style');
					break;
				case 'js':
					$link = $link->withAttribute('as', 'script');
					break;
			}

			$links[] = $link;
		}

		$serializer = new HttpHeaderSerializer();
		$this->response->addHeader('Link', $serializer->serialize($links));
	}

	public function buildStyles(string $bundle): string
	{
		$this->assertBundleExists($bundle);

		$html = '';
		foreach ($this->bundles[$bundle]->getEntries() as $entry) {
			$url = $this->packages->getUrl($entry->getPath(), $entry->getPackageName());

			if ($entry->getType() === 'css') {
				$html .= sprintf('<link rel="stylesheet" href="%s">', htmlspecialchars($url, ENT_QUOTES));
				$html .= "\n";
			}
		}

		return $html;
	}

	public function buildJavascript(string $bundle): string
	{
		$this->assertBundleExists($bundle);

		$html = '';
		foreach ($this->bundles[$bundle]->getEntries() as $entry) {
			$url = $this->packages->getUrl($entry->getPath(), $entry->getPackageName());

			if ($entry->getType() === 'js') {
				$html .= sprintf('<script src="%s"></script>', htmlspecialchars($url, ENT_QUOTES));
				$html .= "\n";
			}
		}

		return $html;
	}

}
