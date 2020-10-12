<?php declare(strict_types = 1);

namespace WebChemistry\Asset;

use Nette\SmartObject;

final class AssetBundleEntry
{

	use SmartObject;

	private ?string $packageName;

	private string $path;

	private string $type;

	public function __construct(?string $packageName, string $path, string $type)
	{
		$this->packageName = $packageName;
		$this->path = $path;
		$this->type = $type;
	}

	public function getPackageName(): ?string
	{
		return $this->packageName;
	}

	public function getPath(): string
	{
		return $this->path;
	}

	/**
	 * css, js
	 */
	public function getType(): string
	{
		return $this->type;
	}

}
