<?php declare(strict_types = 1);

namespace Adeira\Bridges\WorkflowDI;

use Adeira\Workflow\Activity;
use Adeira\Workflow\Transition;

/**
 * Inspiration:
 * - https://www.odoo.com/documentation/8.0/howtos/backend.html#workflows
 * - https://www.odoo.com/documentation/8.0/reference/workflows.html
 */
class WorkflowExtension extends \Nette\DI\CompilerExtension
{

	public function loadConfiguration()
	{
		parent::loadConfiguration();
		$builder = $this->getContainerBuilder();
		$config = $this->getConfig();

		foreach ($config as $workflowName => $workflow) {

			//Graph nodes
			$activities = [];
			$activityConfigNames = [];
			foreach ($workflow['activities'] as $activityConfigName => $activityIdentifier) {
				$activityConfigNames[$activityConfigName] = $activityIdentifier;
				$def = $builder->addDefinition($this->prefix('activity.' . $activityConfigName))
					->setClass(Activity::class, [
						$activityIdentifier,
					]);
				$activities[] = $def;
			};

			//Graph paths
			$transitionsConfig = $workflow['transitions'];
			$alwaysInvoke = NULL;
			if (isset($transitionsConfig['alwaysInvoke'])) {
				$alwaysInvoke = $transitionsConfig['alwaysInvoke'];
				unset($transitionsConfig['alwaysInvoke']);
			}

			if (isset($transitionsConfig['matrix'])) {
				foreach ($this->computeCombinationMatrix($transitionsConfig['matrix']) as $transitionFrom => $transitionTo) {
					$transitionsConfig[] = [
						'from' => $transitionFrom,
						'to' => [$transitionTo],
					];
				}
				unset($transitionsConfig['matrix']);
			}

			$transitions = [];
			foreach ($transitionsConfig as $transitionConfigName => $transitionConfig) {
				foreach ($transitionConfig['to'] as $transitionDestination) {
					$def = $builder->addDefinition($this->prefix('transition.' . $transitionConfig['from'] . '.' . $transitionDestination))
						->setClass(Transition::class, [
							$activityConfigNames[$transitionConfig['from']],
							$activityConfigNames[$transitionDestination],
						]);
					if ($alwaysInvoke) {
						$this->setupTransitionInvoke($def, $alwaysInvoke);
					}
					if (isset($transitionConfig['invoke'])) {
						//FIXME: zafixovat metodu ->run() pomocí interface
						$this->setupTransitionInvoke($def, $transitionConfig['invoke']);
					}
					$transitions[] = $def;
				}
			};

			//Workflows
			$builder->addDefinition($this->prefix($workflowName))
				->setClass(\Adeira\Workflow\Workflow::class, [
					$workflowName,
					$activities,
					$transitions,
				]);
		}
	}

	private function computeCombinationMatrix(array $items)
	{
		foreach ($items as $transitionFrom) {
			foreach ($items as $transitionTo) {
				if ($transitionTo !== $transitionFrom) {
					yield $transitionFrom => $transitionTo;
				}
			}
		}
	}

	private function setupTransitionInvoke(\Nette\DI\ServiceDefinition $definition, $invokeMethod)
	{
		//FIXME: zafixovat metodu ->run() pomocí interface
		$definition->addSetup(
			"?->onTransition[] = function (\$fromActivityIdentifier, \$toActivityIdentifier, callable  \$callback = NULL) {\n"
			. "\treturn ?->run(\$fromActivityIdentifier, \$toActivityIdentifier, \$callback);\n"
			. '}',
			['@self', $invokeMethod]
		);
	}

}
