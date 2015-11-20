<?php

namespace Inkl\Core\Loader;

class DiLoader
{

	public static function load($modulePath, $file = 'config/di.php')
	{
		$definitions = [];
		foreach (new \DirectoryIterator($modulePath) as $info)
		{
			if (!$info->isDot() && $info->isDir() && file_exists($info->getRealPath() . '/' . $file))
			{

				$definitionData = require $info->getRealPath() . '/' . $file;

				$definitions = array_merge_recursive($definitions, $definitionData);

			}
		}

		return $definitions;
	}

}
