<?php declare(strict_types = 1);

namespace WebChemistry\Asset\DI;

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
use WebChemistry\Asset\Vite\VitePackage;

final class AssetExtension extends CompilerExtension
{

	public function getConfigSchema(): Schema
	{
		return Expect::structure([
			'packages' => Expect::arrayOf(Expect::anyOf(Expect::type(Statement::class), Expect::string())),
			'vite' => Expect::structure([
				'manifests' => Expect::arrayOf(Expect::string())->required(),
				'basePath' => Expect::string()->required(),
				'files' => Expect::arrayOf(Expect::anyOf(Expect::string(), Expect::structure([
					'file' => Expect::string()->required(),
					'as' => Expect::string()->required(),
				])->castTo('array')))->required(),
			])->required(false),
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

		$packages = array_map(
			fn (Statement|string $package): Statement => is_string($package) ? new Statement($package) : $package,
			$config->packages,
		);

		$builder->addDefinition($this->prefix('packages'))
			->setFactory(Packages::class, [Arrays::first($packages), $packages]);

		if ($config->vite) {
			$builder->addDefinition($this->prefix('vite'))
				->setFactory(VitePackage::class, [
					$config->vite->manifests,
					$config->vite->basePath,
					$config->vite->files,
				]);
		}
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

}
