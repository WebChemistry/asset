<?php declare(strict_types = 1);

namespace WebChemistry\Asset\Packages;

use Nette\Http\IRequest;
use Symfony\Component\Asset\Context\ContextInterface;
use Symfony\Component\Asset\PathPackage;
use Symfony\Component\Asset\VersionStrategy\VersionStrategyInterface;

final class BasePathPackage extends PathPackage
{

	public function __construct(
		IRequest $request,
		string $basePath,
		VersionStrategyInterface $versionStrategy,
		ContextInterface $context = null
	)
	{
		$basePath = $request->getUrl()->getBasePath() . ltrim($basePath, '/');

		parent::__construct($basePath, $versionStrategy, $context);
	}

}
