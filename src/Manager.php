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

class Manager {

	/**
	 * An array of paths to files created with file()
	 * and later used by cleanUp().
	 *
	 * @see Manager::file()
	 * @see Manager::cleanUp()
	 * @var array
	 */
	protected static $_files = array();

	/**
	 * Used to determine if `Manager::cleanUp()` has been
	 * registered as a shutdown function.
	 *
	 * @see Manager::register()
	 * @see Manager::cleanUp()
	 * @var boolean
	 */
	protected static $_registered = false;

	/**
	 * Returns a path to a temporary file using the system's temporary directory.
	 * File's at the returned path ar automatically cleaned up if the preserve
	 * option is not set to `true`. No empty file is automatically being
	 * created at the returned path.
	 *
	 * @param array $options Possible options are:
	 *                       - `context` Allows for hinting the nature of the
	 *                          temporary file; files are prefixed by the context given.
	 *                       - `extension` Allows for appending an extension to
	 *                         the file; by default all files are extension-less.
	 *                       - `preserve` Allows for indicating that the file should
	 *                         not be automatically be cleaned up.
	 * @return string Absolute file path.
	 */
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

	/**
	 * Registers `Manager::cleanUp()` as a shutdown function.
	 *
	 * @return void
	 */
	public static function register() {
		if (!static::$_registered) {
			register_shutdown_function('\\temporary\\Manager::cleanUp');
		}
	}

	/**
	 * Deletes any existing files created by `Manager::file()`.
	 *
	 * @return void
	 */
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