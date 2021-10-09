<?php declare(strict_types = 1);

namespace WebChemistry\Asset\StaticAsset;

use Nette\Http\Request;

final class StaticAssetBasePathRouter implements StaticAssetRouterInterface
{

	private string $basePath;

	public function __construct(
		Request $request,
		?string $basePath = null,
	)
	{
		$userPath = $basePath ? trim($basePath, '/') . '/' : '';
		$this->basePath = rtrim($request->getUrl()->getBasePath(), '/') . '/' . $userPath;
	}

	public function route(string $asset): string
	{
		return $this->basePath . ltrim($asset, '/');
	}

}
