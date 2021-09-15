<?php declare(strict_types = 1);

namespace WebChemistry\Asset\Traits;

use LogicException;
use WebChemistry\Asset\Encore\Encore;

trait EncorePresenter
{

	private Encore $encore;

	final public function injectEncore(Encore $encore): void
	{
		$this->encore = $encore;
	}

	protected function encorePreload(string $bundle): void
	{
		$this->encore->preload($this->getHttpResponse(), $bundle);
	}

}
