<?php
namespace Burzum\FileStorage\Lib\Utility;

use Cake\Core\Configure;

/**
 * Utility methods for which I could not find a better place
 *
 * @author Florian Kr�mer
 * @copyright 2012 - 2014 Florian Kr�mer
 * @license MIT
 */
class FileStorageUtils {

/**
 * Return file extension from a given filename
 *
 * @param string
 * @return boolean string or false
 */
	public static function fileExtension($name) {
		$list = explode('.', $name);
		if (count($list) > 1) {
			$ext = $list[count($list) - 1];
			return $ext;
		}
		return false;
	}

/**
 * Builds a semi-random path based on a given string to avoid having thousands of files
 * or directories in one directory. This would result in a slowdown on most file systems.
 *
 * Works up to 5 level deep
 *
 * @throws InvalidArgumentException
 * @param mixed $string
 * @param integer $level 1 to 5
 * @return mixed
 */
	public static function randomPath($string, $level = 3) {
		if (!$string) {
			throw new \InvalidArgumentException('First argument is not a string!');
		}
		$string = crc32($string);
		$decrement = 0;
		$path = null;
		for ($i = 0; $i < $level; $i++) {
			$decrement = $decrement - 2;
			$path .= sprintf("%02d" . DS, substr(str_pad('', 2 * $level, '0') . $string, $decrement, 2));
		}
		return $path;
	}

/**
 * Helper method to trim last trailing slash in file path
 *
 * @param string $path Path to trim
 * @return string Trimmed path
 */
	public static function trimPath($path) {
		$len = strlen($path);
		if ($path[$len - 1] == '\\' || $path[$len - 1] == '/') {
			$path = substr($path, 0, $len - 1);
		}
		return $path;
	}

/**
 * Converts windows to linux pathes and vice versa
 *
 * @param string
 * @return string
 */
	public static function normalizePath($string) {
		if (DS == '\\') {
			return str_replace('/', '\\', $string);
		} else {
			return str_replace('\\', '/', $string);
		}
	}

/**
 * Method to normalize the annoying inconsistency of the $_FILE array structure
 *
 * @link http://www.php.net/manual/en/features.file-upload.multiple.php#109437
 * @param array $array
 * @return array Empty array if $_FILE is empty, if not normalize array of Filedata.{n}
 */
	public static function normalizeGlobalFilesArray($array = null) {
		if (empty($array)) {
			$array = $_FILES;
		}
		$newfiles = array();
		if (!empty($array)) {
			foreach ($array as $fieldname => $fieldvalue) {
				foreach ($fieldvalue as $paramname => $paramvalue) {
					foreach ((array)$paramvalue as $index => $value) {
						$newfiles[$fieldname][$index][$paramname] = $value;
					}
				}
			}
		}
		return $newfiles;
	}

/**
 * Serializes and then hashes an array of operations that are applied to an image
 *
 * @param array $operations
 * @return array
 */
	public static function hashOperations($operations) {
		self::ksortRecursive($operations);
		return substr(md5(serialize($operations)), 0, 8);
	}

/**
 * Generate hashes
 *
 * @param string
 * @return void
 */
	public static function generateHashes($configPath = 'FileStorage') {
		$imageSizes = Configure::read($configPath . '.imageSizes');
		if (is_null($imageSizes)) {
			throw new \RuntimeException(__d('file_storage', 'Image processing configuration in %s is missing!', $configPath . '.imageSizes'));
		}
		self::ksortRecursive($imageSizes);
		foreach ($imageSizes as $model => $version) {
			foreach ($version as $name => $operations) {
				Configure::write($configPath . '.imageHashes.' . $model . '.' . $name, self::hashOperations($operations));
			}
		}
	}

/**
 * Recursive ksort() implementation
 *
 * @param array $array
 * @param integer
 * @return void
 * @link https://gist.github.com/601849
 */
	public static function ksortRecursive(&$array, $sortFlags = SORT_REGULAR) {
		if (!is_array($array)) {
			return false;
		}
		ksort($array, $sortFlags);
		foreach ($array as &$arr) {
			self::ksortRecursive($arr, $sortFlags);
		}
		return true;
	}
}
