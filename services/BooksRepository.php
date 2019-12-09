<?php


namespace Services;

use PDO;

class BooksRepository
{
	/** @var PDO */
	private $connection;

	function __construct()
	{
		$this->connection = Db::getInstance()->connection;
	}

	/**
	 * @return array
	 */
	public function getBooks(): array
	{
		$sql = 'SELECT * FROM books';
		$result = $this->connection->query($sql, PDO::FETCH_ASSOC);
		return $result->fetchAll();
	}

	/**
	 * @param int $id
	 * @return array
	 */
	public function getBook(int $id): array
	{
		$sql = "SELECT * FROM books WHERE id = $id";
		$result = $this->connection->query($sql, PDO::FETCH_ASSOC)->fetchAll();
		return $result ? reset($result) : [];
	}

	public function createBook($params)
	{
		$disabled = ['id', 'genres', 'authors'];
		$prepared_params = $this->prepareInsert($params, $disabled);
		$sql = "INSERT INTO books (publishing, name, year, words, cost) VALUES(:publishing, :name, :year, :words, :cost)";
		$sql = $this->connection->prepare($sql);
		$sql->execute($prepared_params);
		$id = $this->connection->lastInsertId();
		$this->bindAuthors($params['authors'], $id);
		$this->bindGenres($params['genres'], $id);
		return $id;
	}

	public function updateBook($params)
	{
		$disabled = ['id', 'genres', 'authors'];
		$keys_string = $this->prepareKeys($params, $disabled);
		$sql = "UPDATE `books` SET $keys_string WHERE `id` = :book_id";
		$sql = $this->connection->prepare($sql);
		$prepared_params = $this->prepareInsert($params, $disabled);
		$prepared_params['book_id'] = $params['id'];
		$sql->execute($prepared_params);
		if (!empty($params['authors'])) {
			$this->cleanAuthors($params['id']);
			$this->bindAuthors($params['authors'], $params['id']);
		}
		if (!empty($params['genres'])) {
			$this->cleanGenres($params['id']);
			$this->bindGenres($params['genres'], $params['id']);
		}
	}

	public function searchBook($params)
	{
		$sql = 'SELECT * FROM books ';
		$bindings = [];
		if (!empty($params)) {
			$where = [];
			$joins = [];
			foreach ($params as $key => $param) {
				switch ($key) {
					case 'author' :
						array_push($where, 'book_authors.author_id IN (:authors_p)');
						array_push($joins, 'LEFT JOIN book_authors ON books.id = book_authors.book_id');
						array_push($joins, 'LEFT JOIN authors ON book_authors.author_id = authors.id');
						$bindings[':authors_p'] = $param;
						break;
					case 'genres' :
						array_push($where, 'book_genres.genre_id IN (:genres_p)');
						array_push($joins, 'LEFT JOIN book_genres ON books.id = book_genres.book_id');
						array_push($joins, 'LEFT JOIN genres ON book_genres.genre_id = genres.id');
						$bindings[':genres_p'] = $param;
						break;
					case 'name' :
						array_push($where, "books.name LIKE :name_p");
						$bindings[':name_p'] = $param;
						break;
					default :
						break;
				}
			}

			$joins = implode(' ', $joins);
			$where = ' WHERE ' . implode(' AND ', $where);
			$sql = $sql . $joins . $where;
		}
		$result = $this->connection->prepare($sql);
		$result->execute($bindings);
		return $result->fetchAll(PDO::FETCH_ASSOC);
	}

	private function prepareKeys($params, $disabled)
	{
		$keys = array_keys($params);
		$keys_string = '';
		foreach ($keys as $key) {
			if (in_array($key, $disabled))
				continue;
			$keys_string .= "`$key` = :$key,";
		}
		$keys_string = rtrim($keys_string, ',');
		return $keys_string;
	}

	private function cleanAuthors($id)
	{
		$sql = "DELETE FROM book_authors WHERE book_id = ?";
		$sql = $this->connection->prepare($sql);
		$sql->execute([$id]);
	}

	private function cleanGenres($id)
	{
		$sql = "DELETE FROM book_genres WHERE book_id = ?";
		$sql = $this->connection->prepare($sql);
		$sql->execute([$id]);
	}

	private function bindAuthors($authors, $id)
	{
		foreach ($authors as $author) {
			$sql = "INSERT INTO book_authors (author_id, book_id) VALUES(?, ?)";
			$sql = $this->connection->prepare($sql);
			$sql->execute([$author, $id]);
		}
	}

	private function bindGenres($genres, $id)
	{
		foreach ($genres as $genre) {
			$sql = "INSERT INTO book_genres (genre_id, book_id) VALUES(?, ?)";
			$sql = $this->connection->prepare($sql);
			$sql->execute([$genre, $id]);
		}
	}

	private function prepareInsert($params, $disabled = [])
	{
		$result = [];
		foreach ($params as $key => $param) {
			if (!empty($disabled) && in_array($key, $disabled))
				continue;
			$result[$key] = $param;
		}

		return $result;
	}
}