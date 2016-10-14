<?php

namespace Alquemedia_PostREST;

class Validator
{
	/**
	* Exception class to be thrown
	*
	* @var string
	*/
	protected static $exceptionClass = "\Exception";

	/**
	* Create and return an Exception
	*
	* @param string $message
	* @return Exception
	*/
	protected static function createException($message)
	{
		return new static::$exceptionClass($message);
	}

	/**
	* Validate a given value to be not null
	*
	* @param mixed $value
	* @param string $message
	* @throws The given exception
	*/
	public static function notNull($value, $message)
	{
		if (is_null($value))
		{
			throw static::createException($message);
		}
	}

	/**
	* Validate a string to be not blank
	*
	* @param string $value
	* @param string $message
	* @throws The given exception
	*/
	public static function notBlank($value, $message)
	{
		if (strcmp(trim($value), "") === 0)
		{
			throw static::createException($message);
		}
	}

	/**
	* Validate a given value to be a positive integer
	*
	* @param mixed $value
	* @param string $message
	* @throws The given exception
	*/
	public static function isPositiveInt($value, $message)
	{
		$tmp = (int) $value;

		if ($tmp != $value || $value <= 0)
		{
			throw static::createException($message);	
		}
	}
	
}