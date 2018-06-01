<?php

namespace App;

/**
 * String handling methods.
 *
 * PHP versions 4 and 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2010, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2010, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       cake
 * @subpackage    cake.cake.libs
 * @since         CakePHP(tm) v 1.2.0.5551
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

/**
 * String handling methods.
 *
 *
 * @package       cake
 * @subpackage    cake.cake.libs
 */
class CakePHPString {

/**
 * Tokenizes a string using $separator, ignoring any instance of $separator that appears between
 * $leftBound and $rightBound
 *
 * @param string|array $data The data to tokenize
 * @param string $separator The token to split the data on.
 * @param string $leftBound The left boundary to ignore separators in.
 * @param string $rightBound The right boundary to ignore separators in.
 * @return array Array of tokens in $data.
 * @access public
 * @static
 */
	public static function tokenize($data, $separator = ',', $leftBound = '(', $rightBound = ')') {
		if (empty($data) || is_array($data)) {
			return $data;
		}

		$depth = 0;
		$offset = 0;
		$buffer = '';
		$results = array();
		$length = strlen($data);
		$open = false;

		while ($offset <= $length) {
			$tmpOffset = -1;
			$offsets = array(
				strpos($data, $separator, $offset),
				strpos($data, $leftBound, $offset),
				strpos($data, $rightBound, $offset)
			);
			for ($i = 0; $i < 3; $i++) {
				if ($offsets[$i] !== false && ($offsets[$i] < $tmpOffset || $tmpOffset === -1)) {
					$tmpOffset = $offsets[$i];
				}
			}
			if ($tmpOffset !== -1) {
				$buffer .= substr($data, $offset, $tmpOffset - $offset);
				if (!$depth && $data{$tmpOffset} === $separator) {
					$results[] = $buffer;
					$buffer = '';
				} else {
					$buffer .= $data{$tmpOffset};
				}
				if ($leftBound !== $rightBound) {
					if ($data{$tmpOffset} === $leftBound) {
						$depth++;
					}
					if ($data{$tmpOffset} === $rightBound) {
						$depth--;
					}
				} elseif ($data{$tmpOffset} === $leftBound) {
                    if (!$open) {
                        $depth++;
                        $open = true;
                    } else {
                        $depth--;
                        $open = false;
                    }
                }
				$offset = ++$tmpOffset;
			} else {
				$results[] = $buffer . substr($data, $offset);
				$offset = $length + 1;
			}
		}
		if (empty($results) && !empty($buffer)) {
			$results[] = $buffer;
		}

		if (!empty($results)) {
			$data = array_map('trim', $results);
		} else {
			$data = array();
		}
		return $data;
	}

/**
 * Replaces variable placeholders inside a $str with any given $data. Each key in the $data array
 * corresponds to a variable placeholder name in $str.
 * Example: `String::insert(':name is :age years old.', array('name' => 'Bob', '65'));`
 * Returns: Bob is 65 years old.
 *
 * Available $options are:
 *
 * - before: The character or string in front of the name of the variable placeholder (Defaults to `:`)
 * - after: The character or string after the name of the variable placeholder (Defaults to null)
 * - escape: The character or string used to escape the before character / string (Defaults to `\`)
 * - format: A regex to use for matching variable placeholders. Default is: `/(?<!\\)\:%s/`
 *   (Overwrites before, after, breaks escape / clean)
 * - clean: A boolean or array with instructions for String::cleanInsert
 *
 * @param string $str A string containing variable placeholders
 * @param string|array  $data A key => val array where each key stands for a placeholder variable name
 *     to be replaced with val
 * @param string $options An array of options, see description above
 * @return string
 * @access public
 * @static
 */
	public static function insert($str, $data, $options = array()) {
		$defaults = array(
			'before' => ':', 'after' => null, 'escape' => '\\', 'format' => null, 'clean' => false
		);
		$options += $defaults;
		$format = $options['format'];
		$data = (array)$data;
		if (empty($data)) {
			return $options['clean'] ? static::cleanInsert($str, $options) : $str;
		}

		if (!isset($format)) {
			$format = sprintf(
				'/(?<!%s)%s%%s%s/',
				preg_quote($options['escape'], '/'),
				str_replace('%', '%%', preg_quote($options['before'], '/')),
				str_replace('%', '%%', preg_quote($options['after'], '/'))
			);
		}

		if (strpos($str, '?') !== false && is_numeric(key($data))) {
			$offset = 0;
			while (($pos = strpos($str, '?', $offset)) !== false) {
				$val = array_shift($data);
				$offset = $pos + strlen($val);
				$str = substr_replace($str, $val, $pos, 1);
			}
			return $options['clean'] ? static::cleanInsert($str, $options) : $str;
		}

        asort($data);

        $hashKeys = array();
        foreach ($data as $key => $value) {
            $hashKeys[] = crc32($key);
        }

        $tempData = array_combine(array_keys($data), array_values($hashKeys));
        krsort($tempData);
        foreach ($tempData as $key => $hashVal) {
            $key = sprintf($format, preg_quote($key, '/'));
            $str = preg_replace($key, $hashVal, $str);
        }
        foreach (array_combine($hashKeys, array_values($data)) as $tmpHash => $tmpValue) {
            $tmpValue = is_array($tmpValue) ? '' : $tmpValue;
            $str = str_replace($tmpHash, $tmpValue, $str);
        }

        if (!isset($options['format']) && isset($options['before'])) {
			$str = str_replace($options['escape'].$options['before'], $options['before'], $str);
		}
		return $options['clean'] ? static::cleanInsert($str, $options) : $str;
	}

/**
 * Cleans up a static::insert() formated string with given $options depending on the 'clean' key in
 * $options. The default method used is text but html is also available. The goal of this function
 * is to replace all whitespace and uneeded markup around placeholders that did not get replaced
 * by static::insert().
 *
 * @param string $str
 * @param array $options
 * @return string
 * @access public
 * @static
 * @see static::insert()
 */
	public static function cleanInsert($str, $options) {
		$clean = $options['clean'];
		if (!$clean) {
			return $str;
		}
		if ($clean === true) {
			$clean = array('method' => 'text');
		}
		if (!is_array($clean)) {
			$clean = array('method' => $options['clean']);
		}
		switch ($clean['method']) {
			case 'html':
				$clean = array_merge(array(
					'word' => '[\w,.]+',
					'andText' => true,
					'replacement' => '',
				), $clean);
				$kleenex = sprintf(
					'/[\s]*[a-z]+=(")(%s%s%s[\s]*)+\\1/i',
					preg_quote($options['before'], '/'),
					$clean['word'],
					preg_quote($options['after'], '/')
				);
				$str = preg_replace($kleenex, $clean['replacement'], $str);
				if ($clean['andText']) {
					$options['clean'] = array('method' => 'text');
					$str = static::cleanInsert($str, $options);
				}
				break;
			case 'text':
				$clean = array_merge(array(
					'word' => '[\w,.]+',
					'gap' => '[\s]*(?:(?:and|or)[\s]*)?',
					'replacement' => '',
				), $clean);

				$kleenex = sprintf(
					'/(%s%s%s%s|%s%s%s%s)/',
					preg_quote($options['before'], '/'),
					$clean['word'],
					preg_quote($options['after'], '/'),
					$clean['gap'],
					$clean['gap'],
					preg_quote($options['before'], '/'),
					$clean['word'],
					preg_quote($options['after'], '/')
				);
				$str = preg_replace($kleenex, $clean['replacement'], $str);
				break;
		}
		return $str;
	}
}
