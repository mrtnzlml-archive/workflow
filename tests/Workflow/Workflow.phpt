<?php

namespace Mrtnzlml\Tests\Workflow;

use Mrtnzlml\Workflow\Activity as A;
use Mrtnzlml\Workflow\Transition as T;
use Mrtnzlml\Workflow\Workflow as Wkf;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

/**
 * @testCase
 */
class Workflow extends \Tester\TestCase
{

	public function testBrokenTransitionDefinitions()
	{
		Assert::exception(function () {
			new Wkf('wkf_1', [new A('a_1')], [new T('b_1', 'a_1')]);
		}, \InvalidArgumentException::class, "From activity 'b_1' cannot be used in transition, because it doesn't exist.");
		Assert::exception(function () {
			new Wkf('wkf_1', [new A('a_1')], [new T('a_1', 'b_1')]);
		}, \InvalidArgumentException::class, "To activity 'b_1' cannot be used in transition, because it doesn't exist.");
	}

	/**  ____
	 *  |    |
	 *  v    |
	 * a_1 ‾‾
	 */
	public function testSimpleWorkflow()
	{
		$workflow = new Wkf('wkf_simple', [new A('a_1')], [new T('a_1', 'a_1')]);
		Assert::same(['a_1'], $workflow->getAvailablePaths('a_1'));
		Assert::exception(function () use ($workflow) {
			$workflow->getAvailablePaths('a_2');
		}, \InvalidArgumentException::class, "Activity 'a_2' doesn't exist.");
	}

	/**
	 *  .—> a_1 <—.
	 *  |    |    |
	 *  |    |    |
	 *  |    -—> a_2
	 *  |         |
	 * a_3 <———#——-
	 */
	public function testWorkflow1()
	{
		$t23 = new T('a_2', 'a_3');
		$t23->onTransition[] = function () {
			echo 't23'; //#
		};
		$workflow = new Wkf('wkf_1', [
			new A('a_1'),
			new A('a_2'),
			new A('a_3'),
		], [
			new T('a_1', 'a_2'),
			new T('a_2', 'a_1'),
			$t23,
			new T('a_3', 'a_1'),
		]);
		Assert::same(['a_2'], $workflow->getAvailablePaths('a_1'));
		Assert::same(['a_1', 'a_3'], $workflow->getAvailablePaths('a_2'));
		Assert::same(['a_1'], $workflow->getAvailablePaths('a_3'));

		ob_start();
		$workflow->runTransition('a_2', 'a_3');
		Assert::same('t23', ob_get_clean());

		Assert::exception(
			function () use ($workflow) {
				$workflow->runTransition('a_1', 'a_3');
			},
			\Mrtnzlml\Workflow\Exceptions\LogicException::class,
			"Cannot move between activity 'a_1' and 'a_3' because transition doesn't exist."
		);
	}

}

(new Workflow)->run();
