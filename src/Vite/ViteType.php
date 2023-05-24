<?php declare(strict_types = 1);

namespace WebChemistry\Asset\Vite;

use InvalidArgumentException;

enum ViteType
{

	case Stylesheet;
	case Script;

	public static function create(string $type): self
	{
		return match ($type) {
			'css', 'stylesheet' => self::Stylesheet,
			'script', 'js', 'ts' => self::Script,
			default => throw new InvalidArgumentException(sprintf('Type %s is not supported.', $type)),
		};
	}

}
