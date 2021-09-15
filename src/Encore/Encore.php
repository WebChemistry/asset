<?php declare(strict_types = 1);

namespace WebChemistry\Asset\Encore;

use InvalidArgumentException;
use Nette\Http\IRequest;
use Nette\Http\IResponse;
use Nette\Utils\FileSystem;
use Nette\Utils\Html;
use Nette\Utils\Json;
use WebChemistry\Asset\Builder\JavascriptBuilder;
use WebChemistry\Asset\Builder\PreloadBuilder;
use WebChemistry\Asset\Builder\StyleBuilder;

final class Encore
{

	private array $data;

	private array $configs;

	public function __construct(array $configs, IRequest $request)
	{
		$this->configs = $configs;
		$this->basePath = rtrim($request->getUrl()->getBasePath(), '/');
	}

	/**
	 * @param mixed[] $options
	 */
	public function buildStyles(string $bundle, array $options = []): Html
	{
		$builder = new StyleBuilder();

		foreach ($this->getBundle($bundle)['css'] ?? [] as $value) {
			$builder->addLink($this->basePath . $value);
		}

		return $builder->build();
	}

	/**
	 * @param mixed[] $options
	 */
	public function buildJavascripts(string $bundle, array $options = []): Html
	{
		$builder = new JavascriptBuilder();

		foreach ($this->getBundle($bundle)['js'] ?? [] as $value) {
			$builder->addLink($this->basePath . $value, $options['async'] ?? false, $options['defer'] ?? false);
		}

		return $builder->build();
	}

	public function preload(IResponse $response, string $bundle): void
	{
		$data = $this->getBundle($bundle);
		$builder = new PreloadBuilder();

		foreach ($data['js'] ?? [] as $value) {
			$builder->addJavascriptLink($this->basePath . $value);
		}

		foreach ($data['css'] ?? [] as $value) {
			$builder->addStyleLink($this->basePath . $value);
		}

		$builder->buildToResponse($response);
	}

	private function getBundle(string $bundle): array
	{
		if (!isset($this->data)) {
			$this->data = [];
			foreach ($this->configs as $config) {
				$data = Json::decode(FileSystem::read($config), Json::FORCE_ARRAY)['entrypoints'];
				$this->data = array_merge($this->data, $data);
			}
		}

		if (!isset($this->data[$bundle])) {
			throw new InvalidArgumentException(
				sprintf(
					'Bundle %s not exists, possibilities %s',
					$bundle,
					implode(', ', array_keys($this->data))
				)
			);
		}

		return $this->data[$bundle];
	}

}
