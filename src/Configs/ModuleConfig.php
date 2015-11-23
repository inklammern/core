<?php

namespace Inkl\Core\Configs;

class ModuleConfig {

	private $config;
	/** @var AppConfig */
	private $appConfig;

	/**
	 * ModuleConfig constructor.
	 * @param AppConfig $appConfig
	 */
	public function __construct(AppConfig $appConfig) {
		$this->appConfig = $appConfig;

		$this->load();
	}

	private function load() {

		$file = 'config/config.php';
		$config = [];
		foreach (new \DirectoryIterator($this->appConfig->getModulePath()) as $info)
		{
			if (!$info->isDot() && $info->isDir() && file_exists($info->getRealPath() . '/' . $file))
			{
				$configData = require $info->getRealPath() . '/' . $file;

				$config = array_merge_recursive($config, $configData);
			}
		}

		$this->config = $config;
	}

	public function get($path = '', $default = '') {

		if (!$path) {
			return $this->config;
		}

		$current = &$this->config;
		foreach (explode('/', $path) as $key) {

			if (!isset($current[$key])) return $default;
			$current = &$current[$key];
		}

		return $current;
	}

}
