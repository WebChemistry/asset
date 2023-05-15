<?php declare(strict_types = 1);

namespace WebChemistry\Asset\VersionStrategy;

use Symfony\Component\Asset\Exception\AssetNotFoundException;
use Symfony\Component\Asset\Exception\LogicException;
use Symfony\Component\Asset\Exception\RuntimeException;
use Symfony\Component\Asset\VersionStrategy\VersionStrategyInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class ViteJsonVersionStrategy implements VersionStrategyInterface
{

	/** @var mixed[] */
	private array $manifestData;

	public function __construct(
		private string $manifestPath,
		private string $basePath,
		private ?HttpClientInterface $httpClient = null,
		private bool $strictMode = false,
	)
	{
		$this->basePath = rtrim($this->basePath, '/');

		if (null === $this->httpClient && ($scheme = parse_url($this->manifestPath, \PHP_URL_SCHEME)) && str_starts_with($scheme, 'http')) {
			throw new LogicException(sprintf('The "%s" class needs an HTTP client to use a remote manifest. Try running "composer require symfony/http-client".', self::class));
		}
	}

	public function getVersion(string $path): string
	{
		if ($path === '!env') {
			$this->loadManifestData();

			return isset($this->manifestData['url']) ? 'dev' : 'prod';
		}

		return $this->applyVersion($path);
	}

	public function applyVersion(string $path): string
	{
		return $this->getManifestPath($path) ?: $path;
	}

	private function loadManifestData(): void
	{
		if (!isset($this->manifestData)) {
			if (null !== $this->httpClient && ($scheme = parse_url($this->manifestPath, \PHP_URL_SCHEME)) && str_starts_with($scheme, 'http')) {
				try {
					$this->manifestData = $this->httpClient->request('GET', $this->manifestPath, [
						'headers' => ['accept' => 'application/json'],
					])->toArray();
				} catch (DecodingExceptionInterface $e) {
					throw new RuntimeException(sprintf('Error parsing JSON from asset manifest URL "%s".', $this->manifestPath), 0, $e);
				} catch (ClientExceptionInterface $e) {
					throw new RuntimeException(sprintf('Error loading JSON from asset manifest URL "%s".', $this->manifestPath), 0, $e);
				}
			} else {
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

	private function getManifestPath(string $path): ?string
	{
		$this->loadManifestData();

		if (isset($this->manifestData['url'])) {
			return $this->manifestData['url'] . $path;
		}

		if (isset($this->manifestData[$path]['file'])) {
			return $this->basePath . '/' . $this->manifestData[$path]['file'];
		}

		if ($this->strictMode) {
			$message = sprintf('Asset "%s" not found in manifest "%s".', $path, $this->manifestPath);
			$alternatives = $this->findAlternatives($path, $this->manifestData);
			if (\count($alternatives) > 0) {
				$message .= sprintf(' Did you mean one of these? "%s".', implode('", "', $alternatives));
			}

			throw new AssetNotFoundException($message, $alternatives);
		}

		return null;
	}

	/**
	 * @param mixed[] $manifestData
	 */
	private function findAlternatives(string $path, array $manifestData): array
	{
		$path = strtolower($path);
		$alternatives = [];

		foreach ($manifestData as $key => $value) {
			$lev = levenshtein($path, strtolower($key));
			if ($lev <= \strlen($path) / 3 || false !== stripos($key, $path)) {
				$alternatives[$key] = isset($alternatives[$key]) ? min($lev, $alternatives[$key]) : $lev;
			}

			$lev = levenshtein($path, strtolower($value));
			if ($lev <= \strlen($path) / 3 || false !== stripos($key, $path)) {
				$alternatives[$key] = isset($alternatives[$key]) ? min($lev, $alternatives[$key]) : $lev;
			}
		}

		asort($alternatives);

		return array_keys($alternatives);
	}

}
