<?php declare(strict_types = 1);

namespace WebChemistry\Asset\Latte\Extension;

use Latte\Extension;
use Symfony\Component\Asset\Packages;
use WebChemistry\Asset\Latte\Extension\Node\AssetNode;

final class AssetExtension extends Extension
{

	public function __construct(
		private Packages $packages,
	)
	{
	}

	/**
	 * @return mixed[]
	 */
	public function getProviders(): array
	{
		return [
			'packages' => $this->packages,
		];
	}

	/**
	 * @return callable[]
	 */
	public function getFunctions(): array
	{
		return [
			'asset' => $this->packages->getUrl(...),
		];
	}

	/**
	 * @return callable[]
	 */
	public function getTags(): array
	{
		return [
			'asset' => [AssetNode::class, 'create'],
			'n:asset' => [AssetNode::class, 'create'],
		];
	}

}
