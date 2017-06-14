<?php
namespace Locker\Tests;

use Locker\Lock;
use Locker\Status;

class LockTest extends \PHPUnit_Framework_TestCase {
	public function setUp() {
		parent::setUp();

		Lock::reset();
	}
	/**
	 * @expectedException \RuntimeException
	 */
	public function testShouldGenerateExceptionUlock() {
		Lock::unlock();
	}

	/**
	 * @expectedException \RuntimeException
	 */
	public function testShouldGenerateExceptionDoubleLock() {
		Lock::lock();
		Lock::lock();
	}

	public function testShouldLock() {
		$this->assertEquals(Lock::lock(), Status::BUSY);
		$this->assertEquals(Lock::unlock(), Status::FREE);

		$this->assertEquals(Lock::lock(), Status::BUSY);
		$this->assertEquals(Lock::unlock(), Status::FREE);
	}

	public function testShouldLockUnlockWithResourceName() {
		Lock::lock(get_called_class());
		Lock::unlock();
		Lock::lock(get_called_class());
		Lock::unlock();
	}
}