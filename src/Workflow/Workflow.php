<?php

namespace Mrtnzlml\Workflow;

/**
 * @see https://www.odoo.com/documentation/8.0/reference/workflows.html
 */
class Workflow// implements IRunable
{

	use \Nette\SmartObject;

	private $activities = []; //activities are only 'dummy' at this moment

	private $transitions = [];

	/**
	 * vertical - from
	 * horizontal - to
	 *
	 *      a1  a2  a3
	 * a1   0   1   0
	 * a2   0   0   0
	 * a3   0   0   0
	 */
	private $graphMatrix = [];

	public function __construct($workflowIdentifier, array $activities, array $transitions)
	{
		/** @var Activity $activity */
		foreach ($activities as $activity) {
			$this->activities[$activity->getIdentifier()] = $activity;
		}

		foreach ($this->activities as $rowIndex => $_) {
			foreach ($this->activities as $columnIndex => $__) {
				$this->graphMatrix[$rowIndex][$columnIndex] = FALSE;
			}
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

			$this->graphMatrix[$fromActivity][$toActivity] = TRUE;
		}

		$this->transitions = $transitions;
	}

	public function runFromActivity($fromActivityIdentifier)
	{
		//compute order of transitions:
		$childActivitiesBuffer = [];
		$childActivitiesBuffer[$fromActivityIdentifier] = $fromActivityIdentifier;
		/** @var Transition $transition */
		foreach ($this->transitions as $transition) {
			if (in_array($transition->getFromActivity(), $childActivitiesBuffer)) {
				$childActivitiesBuffer[$transition->getToActivity()] = $transition->getToActivity();
			}
		}
		foreach ($this->transitions as $transition) { //eager activity binding (transition created before path to the activity)
			if (!in_array($transition->getToActivity(), $childActivitiesBuffer)) {
				$childActivitiesBuffer[$transition->getToActivity()] = $transition->getToActivity();
			}
		}

		//run transitions:
//		var_dump(implode(', ', $childActivitiesBuffer)); //a_1, a_2, a_3, a_4, a_5, a_6, a_7, a_8, a_9
		foreach ($childActivitiesBuffer as $activityIdentifier) {
			$evaluationResult = $this->activities[$activityIdentifier]->run();
			if ($evaluationResult === FALSE) {
				//TODO: odpojit podstrom s rodičem $activityIdentifier
//				foreach($this->graphMatrix[$activityIdentifier] as $columKey => $_) {
//					if($_ === TRUE) {
//						unset($childActivitiesBuffer[$columKey]);
//					}
//				}
//				var_dump($this->getAvailablePaths($activityIdentifier));
			}
		}
	}

	public function runTransition($fromActivityIdentifier, $toActivityIdentifier, callable  $callback = NULL)
	{
		if (!$this->transitionExists($fromActivityIdentifier, $toActivityIdentifier)) {
			throw new \Mrtnzlml\Workflow\Exceptions\LogicException(
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
		foreach ($this->graphMatrix[$fromActivityIdentifier] as $activityIdentifier => $pathExists) {
			if ($pathExists === TRUE) {
				$paths[] = $activityIdentifier;
			}
		}
		return $paths;
	}

	public function transitionExists($fromActivityIdentifier, $toActivityIdentifier)
	{
		if ($this->graphMatrix[$fromActivityIdentifier][$toActivityIdentifier] === TRUE) {
			return TRUE;
		}
		return FALSE;
	}

	public function getGraphMatrix()
	{
		return $this->graphMatrix;
	}

}
