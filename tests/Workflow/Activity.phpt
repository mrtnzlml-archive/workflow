<?php

namespace Adeira\Tests\Workflow;

use Adeira\Workflow\Activity as A;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

/**
 * @testCase
 */
class Activity extends \Tester\TestCase
{

	public function testIdentifier()
	{
		$activity = new A('identifier');
		Assert::same('identifier', $activity->getIdentifier());
		Assert::same('dummy', $activity->getType());

		Assert::exception(function () use ($activity) {
			$activity->undeclared = 'undeclared';
		}, \Nette\MemberAccessException::class, 'Cannot write to an undeclared property ' . A::class . '::$undeclared.');
		Assert::exception(function () use ($activity) {
			$activity->undeclared;
		}, \Nette\MemberAccessException::class, 'Cannot read an undeclared property ' . A::class . '::$undeclared.');
	}

}

(new Activity)->run();
