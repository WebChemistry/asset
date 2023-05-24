<?php declare(strict_types = 1);

namespace WebChemistry\Asset\Latte\Extension;

use Latte\Extension;
use Symfony\Component\Asset\Packages;
use WebChemistry\Asset\Latte\Extension\Node\AssetNode;
use WebChemistry\Asset\Latte\Extension\Node\RenderViteNode;
use WebChemistry\Asset\Vite\VitePackage;

final class AssetExtension extends Extension
{

	public function __construct(
		private Packages $packages,
		private ?VitePackage $vitePackage = null,
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
			'vitePackage' => $this->vitePackage,
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
		$tags = [
			'asset' => [AssetNode::class, 'create'],
			'n:asset' => [AssetNode::class, 'create'],
		];

		if ($this->vitePackage) {
			$tags['renderVite'] = [RenderViteNode::class, 'create'];
		}

		return $tags;
	}

}
