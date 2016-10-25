<?php
namespace Locker;

class Lock {
	/**
	 * Current locked resource
	 *
	 * @var null
	 */
	protected static $_resource = null;

	protected function __construct() {

	}

	protected function __clone() {

	}

	/**
	 * Lock resource
	 * @param mixed $resource
	 * @return int
	 * @throws \RuntimeException
	 */
	public static function lock($resource = null) {
		if (!empty(static::$_resource)) {
			throw new \RuntimeException("Already locked {$resource}");
		}

		if (empty($resource)) {
			$resource = tempnam(sys_get_temp_dir(), 'lock_');
		} else {
			$resource = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'lock_' . $resource;
		}

		if (!touch($resource)) {
			throw new \RuntimeException("Unable to create resource '{$resource}'");
		}
		echo $resource . PHP_EOL;

		if ((static::$_resource = fopen($resource, 'r')) === false) {
			throw new \RuntimeException("Unable to open resource '{$resource}'");
		}

		if (!flock(static::$_resource, LOCK_EX + LOCK_NB)) {
			echo "LOCKED" . PHP_EOL;
			throw new \RuntimeException("Unable to lock {$resource}");
		} else {
			echo "OK" . PHP_EOL;
		}

		return Status::BUSY;
	}

	/**
	 * Unlock
	 * @return int
	 * @throws \RuntimeException
	 */
	public static function unlock() {
		if (empty(static::$_resource)) {
			throw new \RuntimeException("No lock");
		}

		if (!flock(static::$_resource, LOCK_UN)) {
			throw new \RuntimeException("Unable to unlock");
		}

		return static::reset();
	}

	/**
	 * Reset current lock
	 */
	public static function reset() {
		if (!empty(static::$_resource)) {
			@fclose(static::$_resource);
		}

		static::$_resource = null;

		return Status::FREE;
	}
}