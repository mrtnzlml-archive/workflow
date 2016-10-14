<?php

namespace Adeira\Workflow;

use Nette\Utils\ObjectMixin;

trait TStrict
{

	/**
	 * @throws \Nette\MemberAccessException
	 */
	public function __call($name, $args)
	{
		ObjectMixin::strictCall(get_class($this), $name);
	}

	/**
	 * @throws \Nette\MemberAccessException
	 */
	public static function __callStatic($name, $args)
	{
		ObjectMixin::strictStaticCall(get_called_class(), $name);
	}

	/**
	 * @throws \Nette\MemberAccessException
	 */
	public function & __get($name)
	{
		ObjectMixin::strictGet(get_class($this), $name);
	}

	/**
	 * @throws \Nette\MemberAccessException
	 */
	public function __set($name, $value)
	{
		ObjectMixin::strictSet(get_class($this), $name);
	}

}
