<?php

namespace Adeira\Tests\Workflow;

use Adeira\Workflow\Transition as T;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

/**
 * @testCase
 */
class Transition extends \Tester\TestCase
{

	public function testStrictness()
	{
		$transition = new T('from', 'to');

		Assert::exception(function () use ($transition) {
			$transition->undeclared = 'undeclared';
		}, \Nette\MemberAccessException::class, 'Cannot write to an undeclared property ' . T::class . '::$undeclared.');
		Assert::exception(function () use ($transition) {
			$transition->undeclared;
		}, \Nette\MemberAccessException::class, 'Cannot read an undeclared property ' . T::class . '::$undeclared.');
	}

	public function testActivities()
	{
		$transition = new T('from', 'to');
		Assert::same('from', $transition->getFromActivity());
		Assert::same('to', $transition->getToActivity());
	}

}

(new Transition)->run();
