<?php

namespace SS6\ShopBundle\Model\Grid;

use SS6\ShopBundle\Model\Grid\Grid;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Router;
use Twig_Environment;

class GridView {

	/**
	 * @var \SS6\ShopBundle\Model\Grid\Grid
	 */
	private $grid;

	/**
	 * @var array
	 */
	private $templateParameters;

	/**
	 * @var \Twig_Template[]
	 */
	private $templates;

	/**
	 * @var string|string[]|null
	 */
	private $theme;

	/**
	 * @var \Symfony\Component\HttpFoundation\Request
	 */
	private $request;

	/**
	 * @var \Symfony\Component\Routing\Router
	 */
	private $router;

	/**
	 * @var \Twig_Environment
	 */
	private $twig;

	/**
	 * @param \SS6\ShopBundle\Model\Grid\Grid $grid
	 * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
	 * @param \Symfony\Component\Routing\Router $router
	 * @param \Twig_Environment $twig
	 */
	public function __construct(
		Grid $grid,
		RequestStack $requestStack,
		Router $router,
		Twig_Environment $twig,
		$theme,
		array $templateParameters = []
	) {
		$this->grid = $grid;
		$this->request = $requestStack->getMasterRequest();
		$this->router = $router;
		$this->twig = $twig;

		$this->setTheme($theme, $templateParameters);
	}

	public function render() {
		$this->renderBlock('grid');
	}

	/**
	 * @param array|null $removeParameters
	 */
	public function renderHiddenInputs($removeParameters = null) {
		$this->renderBlock('grid_hidden_inputs', [
			'parameter' => $this->grid->getUrlGridParameters(null, $removeParameters),
		]);
	}

	/**
	 * @param string $name
	 * @param array $parameters
	 * @param bool $echo
	 */
	public function renderBlock($name, array $parameters = [], $echo = true) {
		foreach ($this->getTemplates() as $template) {
			if ($template->hasBlock($name)) {
				$templateParameters = array_merge(
					$this->twig->getGlobals(),
					$parameters,
					$this->templateParameters,
					[
						'gridView' => $this,
						'grid' => $this->grid,
					]
				);
				if ($echo) {
					echo $template->renderBlock($name, $templateParameters);
					return;
				} else {
					return $template->renderBlock($name, $templateParameters);
				}
			}
		}

		throw new \InvalidArgumentException(sprintf('Block "%s" doesn\'t exist in grid template "%s".', $name, $this->theme));
	}

	/**
	 * @param \SS6\ShopBundle\Model\Grid\Column $column
	 * @param array|null $row
	 * @param array $formHtmls
	 */
	public function renderCell(Column $column, array $row = null, array $formHtmls = []) {
		if (array_key_exists($column->getId(), $formHtmls)) {
			$formHtml = $formHtmls[$column->getId()];
		} else {
			$formHtml = null;
		}

		if ($row !== null) {
			$value = $this->getCellValue($column, $row);
		} else {
			$value = null;
		}

		$blockParameters = [
			'value' => $value,
			'row' => $row,
		];

		if ($formHtml === null) {
			$posibleBlocks = [
				'grid_value_cell_id_' . $column->getId(),
				'grid_value_cell_type_' . $this->getVariableType($value),
				'grid_value_cell',
			];
		} else {
			$posibleBlocks = [
				'grid_value_cell_edit_id_' . $column->getId(),
				'grid_value_cell_edit_type_' . $this->getVariableType($value),
				'grid_value_cell_edit',
			];
			$blockParameters['formHtml'] = $formHtml;
		}
		foreach ($posibleBlocks as $blockName) {
			if ($this->blockExists($blockName)) {
				$this->renderBlock($blockName, $blockParameters);
				break;
			}
		}
	}

	/**
	 * @param \SS6\ShopBundle\Model\Grid\ActionColumn $actionColumn
	 * @param array $row
	 */
	public function renderActionCell(ActionColumn $actionColumn, array $row) {
		$posibleBlocks = [
			'grid_action_cell_type_' . $actionColumn->getType(),
			'grid_action_cell',
		];
		foreach ($posibleBlocks as $blockName) {
			if ($this->blockExists($blockName)) {
				$this->renderBlock($blockName, ['actionColumn' => $actionColumn, 'row' => $row]);
				break;
			}
		}
	}

	/**
	 * @param \SS6\ShopBundle\Model\Grid\Column $column
	 */
	public function renderTitleCell(Column $column) {
		$posibleBlocks = [
			'grid_title_cell_id_' . $column->getId(),
			'grid_title_cell',
		];
		foreach ($posibleBlocks as $blockName) {
			if ($this->blockExists($blockName)) {
				$this->renderBlock($blockName, ['column' => $column]);
				break;
			}
		}
	}

	/**
	 * @param array $parameters
	 * @param array|string|null $removeParameters
	 * @return string
	 */
	public function getUrl(array $parameters = null, $removeParameters = null) {
		$routeParameters = $this->grid->getUrlParameters($parameters, $removeParameters);

		return $this->router->generate($this->request->attributes->get('_route'), $routeParameters, true);
	}

	/**
	 * @param string $name
	 * @return boolean
	 */
	private function blockExists($name) {
		foreach ($this->getTemplates() as $template) {
			if ($template->hasBlock($name)) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @return string|array
	 */
	public function getTheme() {
		return $this->theme;
	}

	/**
	 * @param string|string[] $theme
	 * @param array $parameters
	 */
	private function setTheme($theme, array $parameters = []) {
		$this->theme = $theme;
		$this->templateParameters = $parameters;
	}

	/**
	 * @return \Twig_Template[]
	 */
	private function getTemplates() {
		if (empty($this->templates)) {
			$this->templates = [];
			if (is_array($this->theme)) {
				foreach ($this->theme as $theme) {
					$this->templates += $this->getTemplatesFromString($theme);
				}
			} else {
				$this->templates = $this->getTemplatesFromString($this->theme);
			}
		}

		return $this->templates;
	}

	/**
	 * @param string $theme
	 * @return \Twig_Template[]
	 */
	private function getTemplatesFromString($theme) {
		$templates = [];

		$template = $this->twig->loadTemplate($theme);
		while ($template != null) {
			$templates[] = $template;
			$template = $template->getParent([]);
		}

		return $templates;
	}

	/**
	 * @param \SS6\ShopBundle\Model\Grid\Column $column
	 * @param array $row
	 * @return mixed
	 */
	private function getCellValue(Column $column, $row) {
		return Grid::getValueFromRowByQueryId($row, $column->getQueryId());
	}

	/**
	 * @param mixed $variable
	 * @return string
	 */
	private function getVariableType($variable) {
		switch (gettype($variable)) {
			case 'boolean':
				return 'boolean';
			case 'integer':
			case 'double':
				return 'number';
			case 'object':
				return str_replace('\\', '_', get_class($variable));
			case 'string':
				return 'string';
			case 'NULL':
				return 'null';
			default:
				return 'unknown';
		}
	}

}
