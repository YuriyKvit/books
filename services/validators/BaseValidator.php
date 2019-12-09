<?php


namespace Services\validators;


use Services\Db;

class BaseValidator
{
	protected $rules;

	protected $request;

	public $messages = [];

	private $property;

	public function validate()
	{
		if (is_array($this->rules)) {
			foreach ($this->rules as $key => $rule) {
				$this->property = $key;
				$property_rules = explode(':', $rule);
				foreach ($property_rules as $property_rule) {
					$property_rule_param = explode('-', $property_rule);
					$func = $property_rule_param[0];
					unset($property_rule_param[0]);
					$params = array_values($property_rule_param);
					if (method_exists($this, $func)) {
						$this->$func(...$params);
					} else {
						error_response('Validator: ' . $property_rule . ' does not exist.');
						exit;
					}
				}
			}
		}
	}

	private function required()
	{
		if (empty($this->request[$this->property])) {
			array_push($this->messages, [$this->property => 'Field is required']);
		}
	}

	private function string($length)
	{
		if (strlen($this->request[$this->property]) > (int)$length) {
			array_push($this->messages, [$this->property => 'String too long']);
		}
	}

	private function array()
	{
		if (!is_array($this->request[$this->property])) {
			array_push($this->messages, [$this->property => 'Field should be array']);
		}

	}

	private function integer()
	{
		if (!is_numeric($this->request[$this->property])) {
			array_push($this->messages, [$this->property => 'Property should be integer type']);
		}
	}

	private function float()
	{
		if (!filter_var($this->request[$this->property], FILTER_VALIDATE_FLOAT)) {
			array_push($this->messages, [$this->property => 'Property should be float type']);
		}

	}

	private function exist($table, $column)
	{
		$db = Db::getInstance();
		if (is_array($this->request[$this->property])) {
			foreach ($this->request[$this->property] as $item) {
				if (!$db->exists($table, $column, $item)) {
					array_push($this->messages, [$this->property => 'Object does not exist']);
				}
			}
		} else {
			if (!$db->exists($table, $column, $this->request[$this->property])) {
				array_push($this->messages, [$this->property => 'Object does not exist']);
			}
		}
	}
}