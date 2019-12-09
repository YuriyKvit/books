<?php

namespace Services\validators;

class BooksValidator extends BaseValidator
{
	public $request;

	public $rules = [
		'name'       => 'string-100',
		'publishing' => 'required:string-100',
		'year'       => 'required:integer',
		'words'      => 'required:integer',
		'cost'       => 'required:float',
		'genres'     => 'required:array:exist-genres-id',
		'authors'    => 'required:array:exist-authors-id'
	];

	public function __construct()
	{
		$this->request = $_POST;
	}

	public function setUpdateRules()
	{
		$keys = array_keys($this->request);
		foreach ($this->rules as $key => $rule) {
			if (!in_array($key, $keys)) {
				unset($this->rules[$key]);
			}
		}
		$this->rules['id'] ='exist-books-id';

	}
}