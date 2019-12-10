<?php

namespace App;

use Services\BooksRepository;
use Services\validators\BooksValidator;

class BooksController extends BaseApi
{

	public function actionIndex()
	{
		try {
			$repository = new BooksRepository();
			return success_response($repository->getBooks());
		} catch (\Throwable $e) {
			return error_response($e->getMessage());
		}
	}

	public function actionView($id)
	{
		try {
			$id = (int)$id;
			$repository = new BooksRepository();
			$result = $repository->getBook($id);
			return $result ? success_response($result) : error_response("Book with ID: $id not found", 404);
		} catch (\Throwable $e) {
			return error_response($e->getMessage());
		}
	}

	public function actionCreate()
	{
		try {
			$validator = new BooksValidator();
			$validator->validate();
			if ($validator->messages) {
				return validator_response($validator->messages);
			}
			$repository = new BooksRepository();
			$id = $repository->createBook($_POST);
			return success_response(['status' => 'success', 'id' => $id]);
		} catch (\Throwable $e) {
			return error_response($e->getMessage());
		}
	}

	public function actionUpdate($id)
	{
		try {
			$_POST['id'] = $id;
			$validator = new BooksValidator();
			$validator->setUpdateRules();
			$validator->validate();
			if ($validator->messages) {
				return validator_response($validator->messages);
			}
			$repository = new BooksRepository();
			$repository->updateBook($_POST);

			return success_response('success');
		} catch (\Throwable $e) {
			return error_response($e->getMessage());
		}
	}

	public function actionSearch($params = [])
	{
		try {
			$repository = new BooksRepository();

			return success_response($repository->searchBook($params));
		} catch (\Throwable $e) {
			return error_response($e->getMessage());
		}
	}

	public function actionAuthors()
	{
		try {
			$repository = new BooksRepository();
			return success_response($repository->getAuthors());
		} catch (\Throwable $e) {
			return error_response($e->getMessage());
		}
	}

	public function actionGenres()
	{
		try {
			$repository = new BooksRepository();
			return success_response($repository->getGenres());
		} catch (\Throwable $e) {
			return error_response($e->getMessage());
		}
	}
}