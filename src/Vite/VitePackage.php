<?php declare(strict_types = 1);

namespace WebChemistry\Asset\Vite;

use Nette\Http\Request;
use Symfony\Component\Asset\Exception\RuntimeException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;

final class VitePackage
{

	/** @var mixed[] */
	private array $manifestData;

	private string $devUrl;

	/**
	 * @param array<string|array{file: string, as: string}> $files
	 */
	public function __construct(
		private string $manifestPath,
		private string $basePath,
		private array $files,
		private Request $request,
	)
	{
	}

	public function getExtras(string $url, string $section, mixed $default = null): array|string|bool|null
	{
		$this->loadManifestData();

		$devUrl = $this->getManifestUrl();

		if ($devUrl) {
			return $default;
		}

		if (isset($this->manifestData[$url][$section])) {
			return $this->manifestData[$url][$section];
		}

		return $default;
	}

	public function getUrl(string $url): ?string
	{
		$this->loadManifestData();

		$devUrl = $this->getManifestUrl();

		if ($devUrl) {
			return $devUrl . $url;
		}

		if (isset($this->manifestData[$url]['file'])) {
			return $this->basePath . '/' . $this->manifestData[$url]['file'];
		}

		return null;
	}

	public function renderToString(): string
	{
		$this->loadManifestData();

		$parts = [];

		$devUrl = $this->getManifestUrl();

		if ($devUrl) {
			$parts[] = $this->createElement($devUrl . '@vite/client', ViteType::Script, true);
		}

		foreach ($this->files as $url) {
			if (is_array($url)) {
				$type = ViteType::create($url['as']);
				$url = $url['file'];
			} else {
				$type = ViteType::Script;
			}

			if ($devUrl) {
				$parts[] = $this->createElement($devUrl . $url, $type, true);
			} else {
				if (isset($this->manifestData[$url]['file'])) {
					$parts[] = $this->createElement($this->basePath . '/' . $this->manifestData[$url]['file'], $type);
				} else {
					trigger_error(sprintf('Asset "%s" was not found in the manifest.', $file), E_USER_WARNING);
				}

				foreach ($this->manifestData[$url]['css'] ?? [] as $stylesheet) {
					$parts[] = $this->createElement($this->basePath . '/' . $stylesheet, ViteType::Stylesheet);
				}
			}
		}

		return implode("\n", $parts);
	}

	private function getManifestUrl(): ?string
	{
		if (!isset($this->devUrl)) {
			$this->loadManifestData();

			$url = $this->manifestData['url'] ?? null;

			if (!$url || !str_starts_with($url, 'http://0.0.0.0:')) {
				return $url;
			}

			return $this->devUrl = sprintf(
				'http://%s%s',
				$this->request->getUrl()->getDomain(),
				substr($url, strlen('http://0.0.0.0')),
			);
		}

		return $this->devUrl;
	}

	private function createElement(string $url, ViteType $type, bool $dev = false): ViteElement
	{
		return match ($type) {
			ViteType::Stylesheet => new ViteStylesheet($url, $dev),
			ViteType::Script => new ViteScript($url),
		};
	}

	private function loadManifestData(): void
	{
		if (!isset($this->manifestData)) {
			if (!is_file($this->manifestPath)) {
				throw new RuntimeException(sprintf('Asset manifest file "%s" does not exist. Did you forget to build the assets with npm or yarn?', $this->manifestPath));
			}

			try {
				$this->manifestData = json_decode(file_get_contents($this->manifestPath), true, flags: \JSON_THROW_ON_ERROR);
			} catch (\JsonException $e) {
				throw new RuntimeException(sprintf('Error parsing JSON from asset manifest file "%s": ', $this->manifestPath).$e->getMessage(), previous: $e);
			}
		}
	}

}
