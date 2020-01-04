<?php declare(strict_types = 1);

namespace WebChemistry\Asset;

use Nette\SmartObject;

final class AssetBundle {

	use SmartObject;

	/** @var string */
	private $name;

	/** @var AssetBundleEntry[] */
	private $entries;

	public function __construct(string $name, array $entries) {
		$this->name = $name;

		foreach ($entries as $entry) {
			$this->addEntry($entry);
		}
	}

	/**
	 * @return AssetBundleEntry[]
	 */
	public function getEntries(): array {
		return $this->entries;
	}

	public function getName(): string {
		return $this->name;
	}

	private function addEntry(AssetBundleEntry $entry): self {
		$this->entries[] = $entry;

		return $this;
	}

}
