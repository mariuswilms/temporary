<?php
/**
 * temporary
 *
 * Copyright (c) 2013 David Persson. All rights reserved.
 *
 * Use of this source code is governed by a BSD-style
 * license that can be found in the LICENSE file.
 */

namespace temporary;

use RuntimeException;

class Manager {

	/**
	 * An array of paths to files created with `Manager::file()`
	 * and later used by `Manager::clean()`.
	 *
	 * @see Manager::file()
	 * @see Manager::clean()
	 * @var array
	 */
	protected static $_clean = [];

	/**
	 * Used to determine if `Manager::clean()` has been
	 * registered as a shutdown function.
	 *
	 * @see Manager::register()
	 * @see Manager::clean()
	 * @var boolean
	 */
	protected static $_registered = false;

	/**
	 * Returns a path to a temporary file using the system's temporary directory.
	 * File's at the returned path ar automatically cleaned up if the clean
	 * option is *not* set to `false`. No empty file is automatically being
	 * created at the returned path.
	 *
	 * @param array $options Possible options are:
	 *                       - `context` Allows for hinting the nature of the
	 *                          temporary file; files are prefixed by the context given.
	 *                       - `extension` Allows for appending an extension to
	 *                         the file; by default all files are extension-less.
	 *                       - `clean` Allows for indicating that the file should
	 *                         not be automatically be cleaned up; defaults to `true`.
	 * @return string|boolean Absolute file path.
	 */
	public static function file(array $options = []) {
		$options += ['context' => null, 'extension' => null, 'clean' => true];

		// Workaround sys_get_temp_dir() not respecting value of ini directive.
		// Let directive be authoratitive.
		$sys = ini_get('sys_temp_dir') ?: sys_get_temp_dir();

		if (!$rSys = realpath($sys)) {
			$message  = "Failed to resolve path to system temporary directory `{$sys}`.";

			if (!file_exists($sys) || !is_dir($sys)) {
				$message .= " Directory does not exist.";
			}
			throw new RuntimeException($message);
		}
		$directory = realpath($rSys) . '/';

		$prefix = null;
		if ($options['context']) {
			$prefix = "{$options['context']}_";
		}
		$file = $directory . uniqid($prefix);

		if ($options['extension']) {
			$file .= ".{$options['extension']}";
		}
		if ($options['clean']) {
			static::$_clean[] = $file;
		}
		return $file;
	}

	/**
	 * Registers `Manager::clean()` as a shutdown function.
	 *
	 * @return void
	 */
	public static function register() {
		if (!static::$_registered) {
			register_shutdown_function('\\temporary\\Manager::clean');
		}
	}

	/**
	 * Deletes any existing files created by `Manager::file()`.
	 *
	 * @return void
	 */
	public static function clean() {
		while ($file = array_pop(static::$_clean)) {
			if (file_exists($file)) {
				unlink($file);
			}
		}
	}
}
Manager::register();

?>