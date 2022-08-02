<?php declare(strict_types = 1);

namespace WebChemistry\Asset\DI;

use LogicException;
use Nette\Bridges\ApplicationLatte\LatteFactory;
use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\FactoryDefinition;
use Nette\DI\Definitions\Statement;
use Nette\DI\MissingServiceException;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use Nette\Utils\Arrays;
use Symfony\Component\Asset\Packages;
use WebChemistry\Asset\Latte\Extension\AssetExtension as LatteAssetExtension;
use WebChemistry\Asset\Package\BasePathPackageFactory;
use WebChemistry\Asset\Package\BaseUrlPackageFactory;
use WebChemistry\Asset\Version\JsonManifestVersionFactory;

final class AssetExtension extends CompilerExtension
{

	public function __construct(
		private ?string $wwwDir = null,
	)
	{
	}

	public function getConfigSchema(): Schema
	{
		return Expect::structure([
			'packages' => Expect::arrayOf(Expect::anyOf(Expect::type(Statement::class), Expect::string())),
		]);
	}

	public function loadConfiguration(): void
	{
		$builder = $this->getContainerBuilder();
		$config = $this->getConfig();

		$builder->addDefinition($this->prefix('latte'))
			->setFactory(LatteAssetExtension::class);

		$builder->addFactoryDefinition($this->prefix('basePath.factory'))
			->setImplement(BasePathPackageFactory::class);

		$builder->addFactoryDefinition($this->prefix('baseUrl.factory'))
			->setImplement(BaseUrlPackageFactory::class);

		$builder->addDefinition($this->prefix('manifest.factory'))
			->setFactory(JsonManifestVersionFactory::class, [$this->getWwwDir()]);

		$packages = array_map(
			fn (Statement|string $package): Statement => is_string($package) ? new Statement($package) : $package,
			$config->packages,
		);

		$builder->addDefinition($this->prefix('packages'))
			->setFactory(Packages::class, [Arrays::first($packages), $packages]);
	}

	public function beforeCompile(): void
	{
		$builder = $this->getContainerBuilder();

		try {
			$factory = $builder->getDefinitionByType(LatteFactory::class);

			assert($factory instanceof FactoryDefinition);

			$factory->getResultDefinition()
				->addSetup('addExtension', [$this->prefix('@latte')]);
		} catch (MissingServiceException) {
			return;
		}
	}

	public function getWwwDir(): ?string
	{
		return $this->wwwDir ??
			   $this->getContainerBuilder()->parameters['wwwDir'] ??
			   throw new LogicException('%wwwDir% is not set.');
	}

}
