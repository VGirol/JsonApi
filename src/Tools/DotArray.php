<?php

namespace VGirol\JsonApi\Tools;

use ArrayAccess;
use Illuminate\Support\Collection;

class DotArray {
    /**
	 * Expand a flattened array with dots keys to a multi-dimensional
	 * associative array.
	 *
	 * @param  ArrayAccess  $array
     *
	 * @return Collection
	 */
	public static function associativeFromDotKeys(ArrayAccess $array): Collection
	{
		$results = array();

		foreach ($array as $value) {
			self::assignArrayByPath($results, $value);
		}

		return collect($results);
	}

	/**
	 * Assigns value to the transformed from string with dots key.
	 *
	 * In a couple of [key => value] assigned value to the key in the form of
	 * multi-dimensional array formed from a string key with dot notation.
	 *
	 * @param  array   $array
	 * @param  string  $path
	 * @param  mixed   $value
     *
	 * @return void
	 */
	protected static function assignArrayByPath(array &$array, string $path, $value = [])
	{
		$keys = explode('.', $path);

		while ($key = array_shift($keys)) {
			$array = &$array[$key];
		}

        $array = $value;
	}

	public static function toDotKeys(ArrayAccess $array): Collection
	{
		$results = array();

		foreach ($array as $key => $value) {
			self::getPaths($results, $key, $value);
		}

		return collect($results);
	}

	protected static function getPaths(array &$array, string $path, $value)
	{
		if (empty($value)) {
            $array[] = $path;
        }

        foreach ($value as $nextKey => $nextValue) {
            self::getPaths($array, $path . '.' . $nextKey, $nextValue);
        }
	}
}
