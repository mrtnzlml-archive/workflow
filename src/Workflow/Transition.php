<?php

namespace Mrtnzlml\Workflow;

/**
 * @method onTransition($fromActivityIdentifier, $toActivityIdentifier, callable $callback)
 */
class Transition
{

	use \Nette\SmartObject;

	public $onTransition = [];

	private $fromActivity;

	private $toActivity;

	public function __construct($fromActivity, $toActivity)
	{
		$this->fromActivity = $fromActivity;
		$this->toActivity = $toActivity;
	}

	public function getFromActivity()
	{
		return $this->fromActivity;
	}

	public function getToActivity()
	{
		return $this->toActivity;
	}

}
