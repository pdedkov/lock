<?php
namespace Locker;

class Lock {
	protected static $_Instance = null;

	/**
	 * Current locked resource
	 *
	 * @var null
	 */
	protected $_resource = null;

	protected function __construct() {

	}

	protected function __clone() {

	}

	/**
	 *
	 * @return \Locker\Lock
	 */
	public static function getInstance() {
		if (static::$_Instance === null) {
			static::$_Instance = new static();
		}

		return static::$_Instance;
	}

	/**
	 * Lock resource
	 * @param mixed $resource
	 * @return int
	 * @throws \RuntimeException
	 */
	public static function lock($resource = null) {
		$_this = static::getInstance();

		if (!empty($_this->_resource)) {
			throw new \RuntimeException("Already locked {$resource}");
		}

		if (!flock($_this->_handler($resource), LOCK_EX | LOCK_NB)) {
			throw new \RuntimeException("Unable to lock {$resource}");
		}

		return Status::BUSY;
	}

	/**
	 * Unlock
	 * @return int
	 * @throws \RuntimeException
	 */
	public static function unlock() {
		$_this = static::getInstance();

		if (empty($_this->_resource)) {
			throw new \RuntimeException("No lock");
		}

		if (!flock($_this->_handler(), LOCK_UN)) {
			throw new \RuntimeException("Unable to unlock '{$_this->_resource}'");
		}

		$_this->_resource = null;

		return Status::FREE;
	}

	/**
	 * Reset current lock
	 */
	public static function reset() {
		$_this = static::getInstance();

		$_this->_resource = null;
	}

	/**
	 * get resource handler
	 *
	 * @param mixed $resource
	 * @return resource
	 * @throws \RuntimeException
	 */
	protected function _handler($resource = null) {
		if (empty($resource) && empty($this->_resource)) {
			$resource = tempnam(sys_get_temp_dir(), 'lock_');
		} elseif (empty($this->_resource)) {
			$resource = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'lock_' . $resource;
		} else {
			$resource = $this->_resource;
		}

		if (!file_exists($resource) && !touch($resource)) {
			throw new \RuntimeException("Unable to create resource '{$resource}'");
		}

		if (($handler = fopen($resource, 'r')) === false) {
			throw new \RuntimeException("Unable to open resource '{$resource}'");
		}

		$this->_resource = $resource;

		return $handler;
	}

	public function __destruct() {
		if (!empty($this->_resource)) {
			@fclose($this->_resource);
		}
	}
}