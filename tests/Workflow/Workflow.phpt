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
		Assert::same([
			'a_1' => [
				'a_1' => TRUE,
			],
		], $workflow->getGraphMatrix());
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

		Assert::same([
			'a_1' => ['a_1' => FALSE, 'a_2' => TRUE, 'a_3' => FALSE],
			'a_2' => ['a_1' => TRUE, 'a_2' => FALSE, 'a_3' => TRUE],
			'a_3' => ['a_1' => TRUE, 'a_2' => FALSE, 'a_3' => FALSE],
		], $workflow->getGraphMatrix());
	}

	/**
	 *             /—> a_6
	 *            /
	 *    /——> a_2 ——> a_4 ——> a_8
	 * a_1
	 *    \——> (a_3) ——> a_5 ——> a_9
	 *     \
	 *      \——> a_7
	 */
	public function testRunWorkflowSimpleWithCondition()
	{
		$callback = function (A $activity) {
			echo $activity->getIdentifier();
		};
		$workflow = new Wkf('qkf', [
			new A('a_9', $callback),
			new A('a_8', $callback),
			new A('a_7', $callback),
			new A('a_6', $callback),
			new A('a_5', $callback),
			(new A('a_4', $callback))->setCondition(function () {
				return TRUE;
			}),
			(new A('a_3', $callback))->setCondition(function () {
				return FALSE;
			}),
			new A('a_2', $callback),
			new A('a_1', $callback),
		], [
			new T('a_4', 'a_8'),
			new T('a_1', 'a_2'),
			new T('a_5', 'a_9'),
			new T('a_1', 'a_3'),
			new T('a_2', 'a_4'),
			new T('a_3', 'a_5'),
			new T('a_2', 'a_6'),
			new T('a_1', 'a_7'),
		]);

		ob_start();
		$workflow->runFromActivity('a_1');
		//without condition: a_1a_2a_3a_4a_5a_6a_7a_8a_9
		Assert::same('a_1a_2a_4a_6a_7a_8', ob_get_clean());
	}

}

(new Workflow)->run();
