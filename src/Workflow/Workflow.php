<?php

namespace Adeira\Workflow;

/**
 * @see https://www.odoo.com/documentation/8.0/reference/workflows.html
 */
class Workflow
{

	use \Nette\SmartObject;

	private $activities = []; //activities are only 'dummy' at this moment

	private $transitions = [];

	public function __construct($workflowIdentifier, array $activities, array $transitions)
	{
		/** @var Activity $activity */
		foreach ($activities as $activity) {
			$this->activities[$activity->getIdentifier()] = $activity;
		}

		/** @var Transition $transition */
		foreach ($transitions as $transition) {
			$fromActivity = $transition->getFromActivity();
			if (!array_key_exists($fromActivity, $this->activities)) {
				throw new \InvalidArgumentException("From activity '$fromActivity' cannot be used in transition, because it doesn't exist.");
			}

			$toActivity = $transition->getToActivity();
			if (!array_key_exists($toActivity, $this->activities)) {
				throw new \InvalidArgumentException("To activity '$toActivity' cannot be used in transition, because it doesn't exist.");
			}
		}
		$this->transitions = $transitions;
	}

	public function runTransition($fromActivityIdentifier, $toActivityIdentifier, callable  $callback = NULL)
	{
		if (!in_array($toActivityIdentifier, $this->getAvailablePaths($fromActivityIdentifier))) {
			throw new \Adeira\Workflow\Exceptions\LogicException(
				"Cannot move between activity '$fromActivityIdentifier' and '$toActivityIdentifier' because transition doesn't exist."
			);
		}

		/** @var Transition $transition */
		foreach ($this->transitions as $transition) {
			//Find the right entity FROM -> TO
			if ($transition->getFromActivity() === $fromActivityIdentifier && $transition->getToActivity() === $toActivityIdentifier) {
				$transition->onTransition($fromActivityIdentifier, $toActivityIdentifier, $callback);
			}
		}
	}

	public function getAvailablePaths($fromActivityIdentifier)
	{
		if (!array_key_exists($fromActivityIdentifier, $this->activities)) {
			throw new \InvalidArgumentException("Activity '$fromActivityIdentifier' doesn't exist.");
		}
		$paths = [];
		/** @var Transition $transition */
		foreach ($this->transitions as $transition) {
			if ($transition->getFromActivity() === $fromActivityIdentifier) {
				$paths[] = $transition->getToActivity();
			}
		}
		return $paths;
	}

}
