<?php declare(strict_types = 1);

namespace WebChemistry\Asset\Package;

use Nette\Http\IRequest;
use Symfony\Component\Asset\Context\ContextInterface;
use Symfony\Component\Asset\UrlPackage;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;
use Symfony\Component\Asset\VersionStrategy\VersionStrategyInterface;

final class BaseUrlPackage extends UrlPackage
{

	public function __construct(
		IRequest $request,
		?string $basePath = null,
		?VersionStrategyInterface $versionStrategy = null,
		ContextInterface $context = null,
	)
	{
		$url = $request->getUrl()->getBaseUrl() . ltrim((string) $basePath, '/');

		parent::__construct($url, $versionStrategy ?? new EmptyVersionStrategy(), $context);
	}

}
