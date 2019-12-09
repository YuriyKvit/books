<?php

namespace Services;

use PDO;
use PDOException;

class Db
{
    public $connection;
    static $_instance;

    private function __Clone()
    {
    }

    private function __construct()
    {
        list('host' => $host, 'name' => $name, 'user' => $user, 'pass' => $pass) = config('db');

        $this->connection = '';
        try {
            $this->connection = new PDO('mysql:host=' . $host . ';dbname=' . $name, $user, $pass, array(PDO::ATTR_PERSISTENT => true));
			$this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die($e->getMessage());
        }
    }

    /**
     * @return Db
     */
    public static function getInstance(): Db
    {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function exists($table, $field, $value)
	{
		$sql = "SELECT 1 FROM $table WHERE $field = :value_n";
		$sql = $this->connection->prepare($sql);
		$sql->bindParam(':value_n', $value, PDO::PARAM_STR, 20);
		$sql->execute();

		return $sql->fetchColumn() ? true : false;

	}
}