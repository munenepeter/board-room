<?php

namespace BoardRoom\Core\Database;


/**
 * @package QueryBuilder
 * 
 * Class that interacts with the db
 * @return \BoardRoom\Models\Model Model returns an instance of Model class
 * 
 * @todo Implement App::get('database')->select('users')->where(['email', $email]);
 * 
 * @author Chungu Developers <developers@chungu.co.ke>
 */

class QueryBuilder {

  protected $pdo;

  public function __construct($pdo) {

    $this->pdo = $pdo;
  }


  public function runQuery(string $sql, string $table) {

    $model = singularize(ucwords($table));

    try {
      $statement = $this->pdo->prepare($sql);
      $statement->execute();
    } catch (\Exception $e) {

      logger("Error", 'Database: ' . $e->getMessage(). PHP_EOL . " $sql ");
      throw new \Exception("Wrong query <br> <pre>{$sql}</pre>" .PHP_EOL. $e->getCode());
    }


    $results = $statement->fetchAll(\PDO::FETCH_CLASS,  "BoardRoom\\Models\\{$model}");

    if (is_null($results) || empty($results)) {
      if (str_contains($sql, "update") || str_contains($sql, "delete")) {
        logger("Warning", "Database: Empty results for your query <br /> <pre>{$sql}</pre>");
        return true;
      }
      return false;
      //   throw new \Exception("There is no results for your query!", 404);
    }
      return  $results;
  }
  /**
   * selectAll
   * 
   * This selects everything from a given table
   * @param string $table table from which to selct the data
   * 
   * @return \BoardRoom\Models\Model returns an instance of Model with the same table name
   */
  public function selectAll(string $table) {

    $sql = "select * from {$table} ORDER BY `created_at` DESC;";

    return $this->runQuery($sql, $table);
  }
  /**
   * Select
   * Selects given values 
   * 
   * @param string $table Table from which to select
   * @param array $values The columns in the db to select from
   * 
   * @return \BoardRoom\Models\Model returns an instance of Model with the same table name
   */
  public function select(string $table, array $values) {

    $values =  implode(',', $values);
    $sql = "select {$values}  from {$table}";
    return $this->runQuery($sql, $table);
  }

  public function selectAllWhereID(string $table, $value) {

    //To do Implement Dynamic Primary key row
    $sql = "select * from {$table} where `id` = \"$value\" ORDER BY `created_at` DESC;";

    return $this->runQuery($sql, $table);
  }

  public function selectAllWhere(string $table, $column, $value, $condition) {

    $sql = "select * from {$table} where `{$column}` $condition \"$value\" ORDER BY `created_at` DESC;";

    return $this->runQuery($sql, $table);
  }


  /**
   * SelectWhere
   * 
   * Selects given column names given a certain condition
   * 
   * @param string $table Table from which to select
   * @param array $values The columns in the db to select from
   * @param array $condition The condition to be fulfiled by the where clause
   * 
   * @example 
   *  selectWhere('table_name", ['email', 'pass'], ['email','test@test.com']);
   */

  public function selectWhere(string $table, array $values, array $condition) {

    $values =  implode(',', $values);

    //pure madness
    $condition[1] = sprintf("%s$condition[1]%s", '"', '"');

    $condition =  implode(' = ', $condition);

    $sql = "select {$values}  from {$table} where {$condition}";

    return $this->runQuery($sql, $table);
  }
  public function update(string $table, $dataToUpdate, $where, $isValue) {
    $sql = "UPDATE {$table} SET $dataToUpdate WHERE `$where` = \"$isValue\"";



    logger("Info", '<b>' . ucfirst(auth()->username) . '</b>' . " Updated a record in {$table} table ");

    return $this->runQuery($sql, $table);
  }
  //DELETE FROM table_name WHERE condition;
  public function delete(string $table, $where, $isValue) {

    $sql = "DELETE FROM {$table} WHERE `$where` = \"$isValue\"";


    logger("Info", '<b>' . ucfirst(auth() ? auth()->username : $_SERVER['REMOTE_ADDR']) . '</b>' . " Deleted a record in {$table} table ");

    return $this->runQuery($sql, $table);
  }
  public function insert(string $table, array $parameters) {

    $sql = sprintf(
      'INSERT INTO %s (%s) VALUES (%s)',

      $table,

      implode(', ', array_keys($parameters)),

      ':' . implode(', :', array_keys($parameters))
    );

    try {

      $statement = $this->pdo->prepare($sql);
      $statement->execute($parameters);

      logger("Info", '<b>' . ucfirst(auth()->username ?? "Someone") . '</b>' . " Inserted a new record to {$table} table ");
    } catch (\Exception $e) {

      logger("Error", "Database: ". $e->getMessage() . ": <br> <pre>{$sql}</pre>");

      throw new \Exception("Database: Error with Query" . $e->getCode());

    }
  }
  //Albtatry Query FROM table_name WHERE condition;
  public function query(string $sql) {

    list($childClass, $caller) = debug_backtrace(false, 2);

    try {
      $statement = $this->pdo->prepare($sql);
      $statement->execute();
    } catch (\Exception $e) {

      logger("Error", 'Database: ' . $e->getMessage(). PHP_EOL . " $sql ");
      throw new \Exception("Wrong query <br> <pre>{$sql}</pre>" .PHP_EOL. $e->getCode());
    }
   
    $results = $statement->fetchAll(\PDO::FETCH_CLASS, $caller['class']);

    if (is_null($results) || empty($results)) {
      if (str_contains($sql, "update") || str_contains($sql, "delete")) {
        logger("Warning", "Database: Empty results for:<br> <pre>{$sql}</pre>");
        return true;
      }      
    }
      return  $results;
  }

  public function queryInsert(string $sql){
    try {

      $statement = $this->pdo->prepare($sql);
      if($statement->execute()){
        logger("Info", '<b>' . ucfirst(auth()->username ?? "Someone") . '</b>' . " Inserted a new record to table ");
      }else{
        logger("Info", '<b>' . ucfirst(auth()->username ?? "Someone") . '</b>' . " something went wrong");

      }
    } catch (\Exception $e) {

      logger("Error", "Database: " . $e->getMessage() . ": <br> <pre>{$sql}</pre>");

      throw new \Exception("Database: Error with Query" . $e->getCode());
    }
  }

  //DELETE FROM table_name WHERE condition;
  public function join(string $table1, string $table2, $fk, $pk) {


    /*
       * SELECT * FROM table1 JOIN table2 ON table1.id1=table2.id2
       */
    $sql = "SELECT * FROM `{$table1}` INNER JOIN `{$table2}` ON {$table1}.{$fk}={$table2}.{$pk} ";


    $statement = $this->pdo->prepare($sql);
    $statement->execute();

    return $statement->fetchAll(\PDO::FETCH_ASSOC);
    
  }

  public function count(string $table, array $condition) {
    //SELECT COUNT(*) FROM $table WHERE $condition[0] = $condition[2];
    list($column, $value) = $condition;
    $sql = "SELECT COUNT(*) AS count FROM $table WHERE $column = \"$value\"";

    return $this->runQuery($sql, $table);
  }
}
