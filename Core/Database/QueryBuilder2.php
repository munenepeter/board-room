<?php

declare(strict_types=1);

class QueryBuilder {

    protected $pdo;
    private $where;
    private $limit;
    private $orderBy;
    private $table;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    /**
     * table
     * @param string $table set the table to perfom queries on
     * 
     * @return self
     */
    public function table($table): self {
        $this->table = $table;
        return $this;
    }
    /**
     * select
     * @param array $columns the columns to select
     * 
     * @return array 
     */
    public function select(array $columns = ['*']): array {
        $columns = implode(', ', $columns);
        $statement = $this->pdo->prepare("SELECT {$columns} FROM {$this->table}");
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_CLASS);
        //return $this;
    }
    /**
     * insert
     * @param array $columns the columns to select
     * 
     * @return string|false last inserted ID or false on failure
     */
    public function insert(array $data): string|false {
        $columns = implode(', ', array_keys($data));
        $values = ":" . implode(', :', array_keys($data));

        try {
            $statement = $this->pdo->prepare("INSERT INTO {$this->table} ({$columns}) VALUES ({$values})");
            $statement->execute($data);
            return $this->pdo->lastInsertId();
        } catch (\PDOException $e) {
            echo $e->getMessage();
            return false;
        }
    }
    /**
     * update
     * 
     * @param array $columns the columns to update
     * 
     * @return bool true on success or false on failure
     */
    public function update(array $data, string $column, string $value, $operator = '='): bool {
        $columns = [];

        foreach ($data as $key => $values) {
            $columns[] = "$key=:$key";
        }
        $columns = implode(', ', $columns);

        try {
            $statement = $this->pdo->prepare("UPDATE {$this->table} SET {$columns} WHERE $column $operator $value");
            $statement->execute($data);
            return (bool)$statement->rowCount();
        } catch (\PDOException $e) {
            echo $e->getMessage();
            return false;
        }
    }

    public function delete($id) {
        $statement = $this->pdo->prepare("DELETE FROM {$this->table} WHERE id = :id");
        $statement->execute(['id' => $id]);
        return $statement->rowCount();
    }

    public function where($column, $value, $operator = '=') {
        $this->where[] = "`$column` $operator '$value'";
        return $this;
    }

    public function orWhere($column, $value, $operator = '=') {
        $this->where[] = "OR `$column` $operator '$value'";
        return $this;
    }

    public function andWhere($column, $value, $operator = '=') {
        $this->where[] = "AND `$column` $operator '$value'";
        return $this;
    }

    public function orderBy($column, $direction = 'ASC') {
        $this->orderBy = "ORDER BY `$column` $direction";
        return $this;
    }

    public function limit($limit, $offset = 0) {
        $this->limit = "LIMIT $limit OFFSET $offset";
        return $this;
    }

    public function get() {
        $where = implode(' ', $this->where);
        $query = "SELECT * FROM {$this->table} WHERE {$where} {$this->orderBy} {$this->limit}";

        try {
            $statement = $this->pdo->prepare($query);
            $statement->execute();
            return $statement->fetchAll(PDO::FETCH_CLASS);
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }
}


/**
 * 
 * 
 * 
 * print_r($db->table("users")
 *            ->update(
 *                   ['gender' => "Male", 'email' => "peter@chungu.co.ke"],
 *                    'id', '500'
 *                 ));
 *
 * $users = $db->table("users")->where('id', '500')->get();
 * echo PHP_EOL;
 * print_r($users);
 * 
 * 
 * 
 * 
 * 
 */