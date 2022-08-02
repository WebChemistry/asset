<?php declare(strict_types = 1);

namespace WebChemistry\Asset\Package;

use Nette\Http\IRequest;
use Symfony\Component\Asset\Context\ContextInterface;
use Symfony\Component\Asset\PathPackage;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;
use Symfony\Component\Asset\VersionStrategy\VersionStrategyInterface;

final class BasePathPackage extends PathPackage
{

	public function __construct(
		IRequest $request,
		?string $basePath,
		?VersionStrategyInterface $versionStrategy,
		ContextInterface $context = null,
	)
	{
		$path = $request->getUrl()->getBasePath() . ltrim((string) $basePath, '/');

		parent::__construct($path, $versionStrategy ?? new EmptyVersionStrategy(), $context);
	}

}
