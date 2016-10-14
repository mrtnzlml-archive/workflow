<?php

namespace Mrtnzlml\Workflow;

class Activity implements IRunable
{

	use TStrict;

	const TYPE_DUMMY = 'dummy';

	private $identifier;

	/**
	 * @var callable
	 */
	private $function = 'pi'; // = NOP

	/**
	 * @var callable
	 */
	private $condition = 'pi'; // = NOP

	/**
	 * @param integer $identifier of the activity
	 * @param \Closure|NULL $function closure to be called after activity initialization
	 */
	public function __construct($identifier, callable $function = NULL)
	{
		$this->identifier = $identifier;
		$this->function = $function;
	}

	public function run()
	{
		if ($this->evaluateCondition()) {
			call_user_func($this->function, $this);
			return TRUE;
		}
		return FALSE;
	}

	public function getType()
	{
		return self::TYPE_DUMMY;
	}

	public function getIdentifier()
	{
		return $this->identifier;
	}

	public function setCondition(callable $condition)
	{
		$this->condition = $condition;
		return $this;
	}

	private function evaluateCondition()
	{
		return call_user_func($this->condition, $this);
	}

}
