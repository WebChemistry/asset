<?php declare(strict_types = 1);

namespace WebChemistry\Asset\Latte;

use InvalidArgumentException;
use Nette\SmartObject;
use WebChemistry\Asset\AssetBundleManager;

final class AssetBundleLatteProvider
{

	use SmartObject;

	private AssetBundleManager $assetBundleManager;

	public function __construct(AssetBundleManager $assetBundleManager)
	{
		$this->assetBundleManager = $assetBundleManager;
	}

	public function build(string $bundle, string $type): string
	{
		if ($type === 'css') {
			return $this->assetBundleManager->buildStyles($bundle);
		} elseif ($type === 'js') {
			return $this->assetBundleManager->buildJavascript($bundle);
		} else {
			throw new InvalidArgumentException(sprintf('Type %s is not supported in macro assetBundle', $type));
		}
	}

}
