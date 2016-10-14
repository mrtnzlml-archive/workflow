<?php

namespace Adeira\Workflow;

/**
 * Workflow activity is at this moment 'dummy' only.
 */
class Activity
{

	use TStrict;

	const TYPE_DUMMY = 'dummy';

	private $identifier;

	public function __construct($identifier)
	{
		$this->identifier = $identifier;
	}

	public function run()
	{
		//TODO: akce, která se spustí vždy když jsem na nějaké akci (zatím jen 'dummy')
	}

	public function getType()
	{
		return self::TYPE_DUMMY;
	}

	public function getIdentifier()
	{
		return $this->identifier;
	}

}
