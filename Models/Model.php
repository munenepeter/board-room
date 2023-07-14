<?php

namespace BoardRoom\Models;

use BoardRoom\Core\Mantle\App;

class Model {
    public function __get($name) {
        return $name;
    }
    public function __set($name, $value) {
        $this->$name = $value;
    }
    public static function getInstance() {
        $model_name = new static;
        return $model_name;
    }


    private static function tableName() {

        //get table name form the model
        $tableName  = get_class(static::getInstance());
        //convert to lowercase and pluralize it
        $tableName  = plural(strtolower($tableName), 2);
        // remove the namespace
        $tableName = substr($tableName, strrpos($tableName, '\\') + 1);

        return $tableName;
        //users
    }

    public static function create(array $data) {

        App::get('database')->insert(static::tableName(), $data);
        //User::create(['name'=>'peter']);
    }
    public static function update($dataToUpdate, $where, $isValue) {

        App::get('database')->update(static::tableName(), $dataToUpdate, $where, $isValue);
        //User::create(['name'=>'peter']);
    }
    /**
     * Delete a record
     * @param string $where name of column in db
     * @param string $isValue value of the column
     * 
     * @return bool
     */
    public static function delete($where, $isValue) {

        return App::get('database')->delete(static::tableName(), $where, $isValue);
        //User::delete('id', 23]);
    }
    public static function all() {
        //Returns all the records in the db for a certain  model/table

        return App::get('database')->selectAll(static::tableName());
        //User::all();
    }
    public static function select($column, $value, $condition = "=") {
        //Returns all the records in the db for a certain  model/table
        return App::get('database')->selectAllWhere(static::tableName(), $column, $condition, $value);
        //User::select('id',89);
    }

    /**
     * Model::Where
     * 
     * Selects given column names given a certain condition
     * 
     * @param array $columns The columns in the db to select from
     * @param array $condition The condition to be fulfiled by the where clause
     * 
     * @example 
     * Model::where(['id', 'name','slug'], ['id', 90]);
     * 
     * @return \BoardRoom\Models\Model;
     */
    public static function where($columns, $condition) {
        //Returns all the records in the db for a certain  model/table

        return  App::get('database')->selectWhere(static::tableName(), $columns, $condition);
        //User::where(['id', 'name','slug'], ['id', 90]); -> return id, name & slug where the id is 90
    }
    public static function query(string $sql) {
        //Returns all the records in the db for a certain  model/table

        return  App::get('database')->query($sql);
        //User::query(Select ,, form , ); 
    }
    public static function count(array $condition) {
        //Returns all the records in the db for a certain  model/table

        return  App::get('database')->count(static::tableName(), $condition)[0]->count;
        //User::query(Select ,, form , ); 
    }
    /**
     * Model::find
     * 
     * Selects every where an id matches the one given
     * 
     * @param int $id the id of the column to be selected
     * 
     * @example 
     * <code>
     *    Model::where(['id', 'name','slug'], ['id', 90]);
     * </code>
     * 
     * 
     * @return \BoardRoom\Models\Model;
     */
    public static function find($id) {

        $item =  App::get('database')->selectAllWhereID(static::tableName(), $id);
        //User::find(1); return a user with the id of 1
        if (empty($item)) {
            throw new \Exception(message: "There is no results for your query!", code: 404);
        }
        return $item[0];
    }
    public function belongsTo($class) {
        // I have no idea to implement relations
        //return a join 
        // $child = static::tableName();
        // $owner = substr($class, strrpos($class, '\\') + 1);

        // $products = array_map(function ($owner) {
        //     $owner->category = $owner::find($products->category_id);
        //     return $products;
        // }, $class::all());
    }
}
