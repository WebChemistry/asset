<?php declare(strict_types = 1);

namespace WebChemistry\Asset\DI;

use Nette\Bridges\ApplicationLatte\ILatteFactory;
use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\FactoryDefinition;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use WebChemistry\Asset\Latte\StaticAssetMacro;
use WebChemistry\Asset\StaticAsset\StaticAssetBasePathRouter;
use WebChemistry\Asset\StaticAsset\StaticAssetRouterInterface;

final class StaticAssetExtension extends CompilerExtension
{

	public function getConfigSchema(): Schema
	{
		return Expect::structure([
			'basePath' => Expect::string()->nullable(),
		]);
	}

	public function loadConfiguration(): void
	{
		$builder = $this->getContainerBuilder();
		$config = $this->getConfig();

		$builder->addDefinition($this->prefix('staticAssetRouter'))
			->setType(StaticAssetRouterInterface::class)
			->setFactory(StaticAssetBasePathRouter::class, ['basePath' => $config->basePath]);
	}

	public function beforeCompile(): void
	{
		$builder = $this->getContainerBuilder();

		if (!interface_exists(ILatteFactory::class)) {
			return;
		}

		$definition = $builder->getDefinitionByType(ILatteFactory::class);
		assert($definition instanceof FactoryDefinition);

		$definition->getResultDefinition()
			->addSetup(
				'$service->onCompile[] = function ($engine) { ?::install($engine->getCompiler()); }',
				[StaticAssetMacro::class]
			)
			->addSetup(
				'addProvider',
				[StaticAssetMacro::LATTE_PROVIDER_NAME, $builder->getDefinition($this->prefix('staticAssetRouter'))]
			);
	}

}
