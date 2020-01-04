<?php declare(strict_types = 1);

namespace WebChemistry\Asset;

use Nette\SmartObject;

final class AssetBundleEntry {

	use SmartObject;

	/** @var string|null */
	private $packageName;

	/** @var string */
	private $path;

	/** @var string */
	private $type;

	public function __construct(?string $packageName, string $path, string $type) {
		$this->packageName = $packageName;
		$this->path = $path;
		$this->type = $type;
	}

	/**
	 * @return string|null
	 */
	public function getPackageName(): ?string {
		return $this->packageName;
	}

	public function getPath(): string {
		return $this->path;
	}

	public function getType(): string {
		return $this->type;
	}

}
