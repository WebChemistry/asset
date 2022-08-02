<?php declare(strict_types = 1);

namespace WebChemistry\Asset\Version;

use Symfony\Component\Asset\Context\ContextInterface;
use Symfony\Component\Asset\Package;
use Symfony\Component\Asset\VersionStrategy\JsonManifestVersionStrategy;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class JsonManifestVersionFactory
{

	public function __construct(
		private string $wwwDir,
	)
	{
		$this->wwwDir = rtrim($this->wwwDir, '/');
	}

	public function create(
		string $manifestPath,
		?HttpClientInterface $httpClient = null,
		bool $strictMode = false,
		?ContextInterface $context = null,
	): Package
	{
		return new Package(
			new JsonManifestVersionStrategy($this->wwwDir . '/' . ltrim($manifestPath, '/'), $httpClient, $strictMode),
			$context,
		);
	}

}
