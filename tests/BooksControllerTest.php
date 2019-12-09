<?php

namespace Tests;

use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;
use Services\BooksRepository;
use Services\Db;

class BooksControllerTest extends TestCase
{
	private $http;
	private $repository;
	private $connection;

	public function setUp()
	{
		$this->http = new Client(['base_uri' => 'http://books.local/api/']);
		$this->repository = new BooksRepository();
		$this->connection = Db::getInstance()->connection;

	}

	/**
	 * @test
	 */
	public function testActionIndex()
	{
		$response = $this->http->request('GET', '/');

		$this->assertEquals(200, $response->getStatusCode());
		$content = json_decode($response->getBody()->getContents(), true);
		$aspect = $this->repository->getBooks();

		$contentType = $response->getHeaders()["Content-Type"][0];
		$this->assertEquals("application/json", $contentType);
		$this->assertEquals(count($content), count($aspect));
	}

	/**
	 * @test
	 */
	public function testActionView()
	{
		$id = $this->makeTestRecord();
		$response = $this->http->request('GET', "books/$id");

		$this->assertEquals(200, $response->getStatusCode());

		$contentType = $response->getHeaders()["Content-Type"][0];
		$this->assertEquals("application/json", $contentType);
		$content = json_decode($response->getBody()->getContents(), true);
		$data = $this->getData();
		unset($data['authors']);
		unset($data['genres']);
		$data['id'] = $id;
		$this->assertEquals($data, $content);
		$this->deleteTestRecord($id);
	}

	/**
	 * @test
	 */
	public function testActionCreate()
	{
		$response = $this->http->request('POST', 'books/create',
			[
				'headers' => ['Content-Type' => 'application/json'],
				'json'    => $this->getData()
			]);

		$this->assertEquals(200, $response->getStatusCode());

		$contentType = $response->getHeaders()["Content-Type"][0];
		$this->assertEquals("application/json", $contentType);
		$content = json_decode($response->getBody()->getContents(), true);
		$this->assertArrayHasKey('id', $content);
		$id = $content['id'];
		$this->deleteTestRecord($id);
	}

	/**
	 * @test
	 */
	public function testActionUpdate()
	{
		$response = $this->http->request('POST', 'books/create',
			[
				'headers' => ['Content-Type' => 'application/json'],
				'json'    => $this->getData()
			]);

		$this->assertEquals(200, $response->getStatusCode());

		$contentType = $response->getHeaders()["Content-Type"][0];
		$this->assertEquals("application/json", $contentType);
		$content = json_decode($response->getBody()->getContents(), true);
		$this->assertArrayHasKey('id', $content);
		$id = $content['id'];
		$data = $this->getData();
		$data['name'] = 'test2';
		$response = $this->http->request('PUT', "books/update/$id",
			[
				'headers' => ['Content-Type' => 'application/json'],
				'json'    => $data
			]);
		$this->assertEquals(200, $response->getStatusCode());

		$contentType = $response->getHeaders()["Content-Type"][0];
		$this->assertEquals("application/json", $contentType);
		$content = json_decode($response->getBody()->getContents(), true);
		$this->assertEquals($content, 'success');
		$response = $this->http->request('GET', "books/$id");

		$this->assertEquals(200, $response->getStatusCode());

		$contentType = $response->getHeaders()["Content-Type"][0];
		$this->assertEquals("application/json", $contentType);
		$content = json_decode($response->getBody()->getContents(), true);
		$this->assertEquals($content['name'], 'test2');
		$this->deleteTestRecord($id);
	}

	/**
	 * @test
	 */
	public function testActionSearch()
	{
		$id = $this->makeTestRecord();
		$response = $this->http->request('GET', 'books/search?name=test');

		$this->assertEquals(200, $response->getStatusCode());

		$contentType = $response->getHeaders()["Content-Type"][0];
		$this->assertEquals("application/json", $contentType);
		$content = json_decode($response->getBody()->getContents(), true);
		$content = reset($content);
		$this->assertEquals($content['name'], 'test');
		$this->deleteTestRecord($id);
	}

	public function tearDown()
	{
		$this->http = null;
	}

	private function getData()
	{
		return [
			"name"       => "test",
			"publishing" => "test",
			"year"       => '1111',
			"words"      => '1111',
			"cost"       => '11.22',
			"genres"     => [1],
			"authors"    => [1]
		];
	}

	private function makeTestRecord()
	{
		$data = $this->getData();
		return $this->repository->createBook($data);
	}

	private function deleteTestRecord($id)
	{
		$sql = "DELETE FROM books WHERE id = ?";
		$sql = $this->connection->prepare($sql);
		$sql->execute([$id]);
	}
}