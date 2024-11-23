<?php declare(strict_types = 1);

namespace WebChemistry\Asset\Vite;

use Nette\Http\IResponse;
use Nette\Http\Request;
use Symfony\Component\Asset\Exception\RuntimeException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;

final class VitePackage
{

	/** @var mixed[] */
	private array $manifestData;

	private string $devUrl;
	
	private ?string $nonce;

	/**
	 * @param string[] $manifests
	 * @param array<string|array{file: string, as: string}> $files
	 */
	public function __construct(
		private array $manifests,
		private string $basePath,
		private array $files,
		private Request $request,
		IResponse $response,
	)
	{
		$this->nonce = $this->findNonce($response);
	}

	private function findNonce(IResponse $response): ?string
	{
		$header = $response->getHeader('Content-Security-Policy')
			?: $response->getHeader('Content-Security-Policy-Report-Only');

		return preg_match('#\s\'nonce-([\w+/]+=*)\'#', (string) $header, $m) ? $m[1] : null;
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
			ViteType::Stylesheet => new ViteStylesheet($url, $dev, $this->nonce),
			ViteType::Script => new ViteScript($url, $this->nonce),
		};
	}

	private function loadManifestData(): void
	{
		if (!isset($this->manifestData)) {
			foreach ($this->manifests as $manifest) {
				if (!is_file($manifest)) {
					continue;
				}

				try {
					$this->manifestData = json_decode(file_get_contents($manifest), true, flags: \JSON_THROW_ON_ERROR);

					return;
				} catch (\JsonException $e) {
					throw new RuntimeException(sprintf('Error parsing JSON from asset manifest file "%s": ', $this->manifestPath).$e->getMessage(), previous: $e);
				}
			}

			throw new RuntimeException(sprintf('Asset manifest files %s do not exist. Did you forget to build the assets with npm or yarn?', implode(', ', $this->manifests)));
		}
	}

}
