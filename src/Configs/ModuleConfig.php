<?php

namespace Inkl\Core\Configs;

class ModuleConfig {

	private $config;

	/**
	 * @param $config
	 */
	public function __construct($config) {
		$this->config = $config;
	}


	public function get($path, $default = '') {

		$current = &$this->config;
		foreach (explode('/', $path) as $key) {

			if (!isset($current[$key])) return $default;
			$current = &$current[$key];
		}

		return $current;
	}

}
