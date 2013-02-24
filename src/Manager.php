<?php

namespace temporary;

class Manager {

	protected static $_files = array();

	protected static $_registered = false;

	public static function file(array $options = array()) {
		$options += array('context' => null, 'extension' => null, 'preserve' => false);

		$directory = realpath(sys_get_temp_dir()) . '/';

		$prefix = null;
		if ($options['context']) {
			$prefix = "{$options['context']}_";
		}
		$file = $directory . uniqid($prefix);

		if ($options['extension']) {
			$file .= ".{$options['extension']}";
		}
		if ($options['preserve']) {
			return $file;
		}
		return static::$_files[] = $file;
	}

	public static function register() {
		if (!static::$_registered) {
			register_shutdown_function('\\temporary\\Manager::cleanUp');
		}
	}

	public static function cleanUp() {
		while ($file = array_pop(static::$_files)) {
			if (file_exists($file)) {
				unlink($file);
			}
		}
	}
}
Manager::register();

?>