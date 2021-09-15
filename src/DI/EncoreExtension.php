<?php declare(strict_types = 1);

namespace WebChemistry\Asset\DI;

use Nette\Bridges\ApplicationLatte\ILatteFactory;
use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\FactoryDefinition;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use WebChemistry\Asset\Encore\Encore;
use WebChemistry\Asset\Latte\EncoreLatteProvider;
use WebChemistry\Asset\Latte\EncoreMacro;

final class EncoreExtension extends CompilerExtension
{

	public function getConfigSchema(): Schema
	{
		return Expect::structure([
			'config' => Expect::anyOf(Expect::string(), Expect::arrayOf(Expect::string()))->required(),
		]);
	}

	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();
		$config = $this->getConfig();

		$builder->addDefinition($this->prefix('encore'))
			->setFactory(Encore::class, [(array) $config->config]);

		if (interface_exists(ILatteFactory::class)) {
			$builder->addDefinition($this->prefix('latteProvider'))
				->setType(EncoreLatteProvider::class);
		}
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
			->addSetup('$service->onCompile[] = function ($engine) { ?::install($engine->getCompiler()); }', [EncoreMacro::class])
			->addSetup('addProvider', [EncoreMacro::LATTE_PROVIDER_NAME, $builder->getDefinition($this->prefix('latteProvider'))]);
	}

}
