<?php declare(strict_types = 1);

namespace WebChemistry\Asset\DI;

use Nette\Bridges\ApplicationLatte\ILatteFactory;
use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\FactoryDefinition;
use Nette\DI\Definitions\Statement;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use WebChemistry\Asset\AssetBundle;
use WebChemistry\Asset\AssetBundleEntry;
use WebChemistry\Asset\AssetBundleManager;
use WebChemistry\Asset\Exceptions\CompilerException;
use WebChemistry\Asset\Latte\AssetBundleLatteProvider;
use WebChemistry\Asset\Latte\AssetBundleMacro;
use WebChemistry\Asset\Latte\AssetMacro;

final class AssetBundleExtension extends CompilerExtension {

	public function getConfigSchema(): Schema
	{
		return Expect::arrayOf(
			Expect::arrayOf(
				Expect::anyOf(Expect::string(), Expect::structure([
					'source' => Expect::string()->required(),
					'package' => Expect::string()->nullable(),
					'type' => Expect::string()->nullable(),
				]))
			)
		);
	}

	public function loadConfiguration(): void
	{
		$builder = $this->getContainerBuilder();
		$config = $this->getConfig();

		$builder->addDefinition($this->prefix('assetBundleManager'))
			->setFactory(AssetBundleManager::class, [$this->entries($config)]);

		if (interface_exists(ILatteFactory::class)) {
			$builder->addDefinition($this->prefix('latteProvider'))
				->setType(AssetBundleLatteProvider::class);
		}
	}

	public function beforeCompile(): void
	{
		$builder = $this->getContainerBuilder();

		if (!interface_exists(ILatteFactory::class)) {
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
			->addSetup('$service->onCompile[] = function ($engine) { ?::install($engine->getCompiler()); }', [AssetBundleMacro::class])
			->addSetup('addProvider', [AssetBundleMacro::LATTE_PROVIDER_NAME, $builder->getDefinition($this->prefix('latteProvider'))]);
	}

	/**
	 * @param mixed $config
	 * @return Statement[]
	 * @throws CompilerException
	 */
	protected function entries(array $config): array
	{
		$classes = [];
		foreach ($config as $name => $items) {
			$classes[$name] = $bundle = new Statement(AssetBundle::class, [$name]);
			$entries = [];
			foreach ($items as $item) {
				if (is_string($item)) {
					$args = $this->resolvePackageAndSource($item);
					$args[] = $this->resolveSuffix($args[1]);

				} else {
					$args = [];
					if ($item->package) {
						$args[0] = $item->package;
						$args[1] = $item->source;
					} else {
						$args = $this->resolvePackageAndSource($item->source);
					}

					if (!$item->type) {
						$args[] = $this->resolveSuffix($item->source);
					} else {
						$args[] = $item->type;
					}

				}

				$entries[] = new Statement(AssetBundleEntry::class, $args);
			}

			$bundle->arguments[] = $entries;
		}

		return $classes;
	}

	private function resolvePackageAndSource(string $item): array
	{
		$args = explode(':', $item, 2);
		$count = count($args);

		if ($count === 1) {
			array_unshift($args, null);
		} else if (!$args[0]) {
			$args[0] = null;
		}

		return $args;
	}

	private function resolveSuffix(string $item): string
	{
		$pos = strrpos($item, '.');
		if ($pos === false) {
			throw new CompilerException(sprintf('Cannot resolve suffix in %s', $item));
		}
		$suffix = substr($item, $pos + 1);

		if (!in_array($suffix, ['css', 'js'])) {
			throw new CompilerException(sprintf('Suffix must be css or js, %s given', $suffix));
		}

		return substr($item, $pos + 1);
	}

}
