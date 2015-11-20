<?php

namespace Inkl\Core\Loader;

use Inkl\Core\Configs\AppConfig;
use Interop\Container\ContainerInterface;

class BootstrapLoader
{
	/** @var ContainerInterface */
	private $container;
	/** @var AppConfig */
	private $appConfig;

	/**
	 * AbstractBootstrap constructor.
	 * @param ContainerInterface $container
	 * @param AppConfig $appConfig
	 */
	public function __construct(ContainerInterface $container, AppConfig $appConfig)
	{
		$this->container = $container;
		$this->appConfig = $appConfig;
	}


	public function load($file = 'bootstrap.php')
	{
		foreach (new \DirectoryIterator($this->appConfig->getModulePath()) as $info)
		{
			if (!$info->isDot() && $info->isDir() && file_exists($info->getRealPath() . '/src/' . $file))
			{
				$className = require $info->getRealPath() . '/src/' . $file;
				$this->container->get($className)->bootstrap();
			}
		}
	}


}
