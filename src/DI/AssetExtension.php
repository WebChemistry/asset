<?php declare(strict_types = 1);

namespace WebChemistry\Asset\DI;

use Nette\Bridges\ApplicationLatte\ILatteFactory;
use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\FactoryDefinition;
use Nette\Http\IRequest;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use Symfony\Component\Asset\PackageInterface;
use Symfony\Component\Asset\Packages;
use WebChemistry\Asset\Packages\BasePathPackage;
use WebChemistry\Asset\Exceptions\CompilerException;
use WebChemistry\Asset\Latte\AssetMacro;

final class AssetExtension extends CompilerExtension {

	public function getConfigSchema(): Schema {
		return Expect::structure([
			'packages' => Expect::arrayOf(
				Expect::structure([
					'type' => Expect::string(),
					'arguments' => Expect::arrayOf('mixed'),
				])
			),
			'macro' => Expect::structure([
				'enable' => Expect::bool(interface_exists(ILatteFactory::class)),
			])
		]);
	}

	public function loadConfiguration() {
		$builder = $this->getContainerBuilder();
		$config = $this->getConfig();

		$default = null;
		$packages =  [];
		foreach ($config->packages as $name => $package) {
			if (!class_exists($package->type)) {
				throw new CompilerException(sprintf('Package class %s not exists', $package->type));
			}

			if ($package->type === BasePathPackage::class) {
				array_unshift($package->arguments, '@' . IRequest::class);
			}

			$def = $builder->addDefinition($this->prefix('package.' . $name))
				->setType(PackageInterface::class)
				->setFactory($package->type, (array) $package->arguments);

			if ($name === 'default') {
				$default = $def;
			} else {
				$packages[$name] = $def;
				$def->setAutowired(false);
			}
		}

		if (!$default) {
			throw new CompilerException('Default package must be set');
		}

		$builder->addDefinition($this->prefix('packages'))
			->setFactory(Packages::class, [
				$default,
				$packages,
			]);
	}

	public function beforeCompile(): void {
		$builder = $this->getContainerBuilder();
		$config = $this->getConfig();

		if (!$config->macro->enable) {
			return;
		}

		$definition = $builder->getDefinitionByType(ILatteFactory::class);

		if (!$definition instanceof FactoryDefinition) {
			throw new CompilerException(
				sprintf(
					'%s definition must be instance of %s, %s given',
					ILatteFactory::class,
					FactoryDefinition::class,
					get_class($definition)
				)
			);
		}

		$definition->getResultDefinition()
			->addSetup('$service->onCompile[] = function ($engine) { ?::install($engine->getCompiler()); }', [AssetMacro::class])
			->addSetup('addProvider', ['assetPackages', $builder->getDefinition($this->prefix('packages'))]);
	}

}
