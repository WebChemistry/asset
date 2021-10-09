<?php declare(strict_types = 1);

namespace WebChemistry\Asset\StaticAsset;

interface StaticAssetRouterInterface
{

	public function route(string $asset): string;

}
