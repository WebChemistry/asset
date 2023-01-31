<?php declare(strict_types = 1);

namespace WebChemistry\Asset\Package;

use Symfony\Component\Asset\Context\ContextInterface;
use Symfony\Component\Asset\VersionStrategy\VersionStrategyInterface;

interface BaseUrlPackageFactory
{

	public function create(
		?string $basePath = null,
		?VersionStrategyInterface $versionStrategy = null,
		ContextInterface $context = null,
		?string $subdomain = null,
	): BaseUrlPackage;

}
