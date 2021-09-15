<?php declare(strict_types = 1);

namespace WebChemistry\Asset\Latte;

use InvalidArgumentException;
use Nette\Utils\Html;
use WebChemistry\Asset\Encore\Encore;

final class EncoreLatteProvider
{

	private Encore $encore;

	public function __construct(Encore $encore)
	{
		$this->encore = $encore;
	}

	public function build(string $bundle, string $type, mixed ... $options): Html
	{
		if ($type === 'css') {
			return $this->encore->buildStyles($bundle, $options);
		} elseif ($type === 'js') {
			return $this->encore->buildJavascripts($bundle, $options);
		} else {
			throw new InvalidArgumentException(sprintf('Type %s is not supported in macro encore', $type));
		}
	}

}
