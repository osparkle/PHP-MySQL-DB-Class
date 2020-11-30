<?php
/**
     * PHP Form Validator class
     *
     *
     * @author Simeon Adedokun <femsimade@gmail.com>
     * @copyright (c) 2020, Simeon Adedokun
     * ====================================================
     * Last modified 30th November, 2020
     *
*/

include "dbcon_params.php";

class DB{
    //database variables
    #host name
    private $dbHost = DB_SERVER;
    #username
    private $dbUser = DB_USERNAME;
    #password
    private $dbPass = DB_PASSWORD;
    #database name
    private $dbName = DB_NAME;
    #time zone
    private $time_zone = (defined("TIME_ZONE") && !empty(TIME_ZONE)) ? TIME_ZONE : "+00:00";
    #table prefix
    private $tbl_prefix = TBL_PREFIX;

    public $link;

    public function __construct()
    {
        $link = new mysqli($this->dbHost,$this->dbUser,$this->dbPass,$this->dbName);
        if($link->connect_error){
            die("Failed to connect to data source: " . $link->connect_error);
        }else{
            $this->db = $link;
        }
        //SET time_zone;
        $settime = $this->db->query("SET time_zone='".$this->time_zone."'") or die($this->db->error);
    }

    /*
     * escape string
     * Escapes special characters in a string for use in an SQL query, taking into account the current character set of the connection.
     * Returns clean string
     * @param value is the string to escape special characters from
    */
    public function escapeString($value)
    {
        return $this->db->escape_string(htmlspecialchars(stripslashes(strip_tags(trim($value)))));
    }

    /*
     * Run an SQL statement directly
     * @param sql_type could be select, selectall, insert, update, delete, numrow
     * @param sql is the well formatted sql statement
     * Returns array of results for type selectall; number of results found for numrows; number of affected rows for update and delete; last record id for insert; array of field=>value for select (select one record)
     * Example: $db->runSQLStatement("select","SELECT * FROM users WHERE `city`='Lagos'")
    */
    public function runSQLStatement($sql_type,$sql)
    {
        $allowed_sql_types = array('select','selectall','insert','update','delete','numrows');
        $query_result = $this->db->query($sql) or die($this->db->error);
        if ($sql_type=='selectall') {
			while($row = $query_result->fetch_assoc()) {
				$result_array[] = $row;
			}
			return !empty($query_result->num_rows > 0) ? $result_array : false;
        }
        elseif ($sql_type=='select') {
            return !empty($query_result->num_rows > 0) ? $query_result->fetch_assoc() : false;
        }
        elseif ($sql_type=='insert') {
            return $query_result ? $this->db->insert_id : false;
        }
        elseif ($sql_type=='update' || $sql_type=='delete') {
            return $query_result ? $this->db->affected_rows : false;
        }
        elseif ($sql_type=='numrows') {
            return $query_result->num_rows;
        }
        else{
            return $query_result ? true : false;
        }
    }

    /*
     * SELECT and return array of all records from a table, or false if no record found
     * @param table is the name of the database table
     * @param comma_sep_fields is a comma-separated list of fields to select
     * @param array or string conditions: array of SELECT clauses or a comma-separated list of clauses
     * Example $db->showAllRecords("users","name,phone,address,city,country",array('city'=>'Lagos',"country"=>"Nigeria"))
     * Returns associative array of results. Use var_dump(result) to check array
    */
    public function showAllRecords($table,$comma_sep_fields="*",$conditions="")
    {
        $this->tblName = $this->tbl_prefix.$table;
        $sql = "SELECT ".$comma_sep_fields." FROM ".$this->tblName;
        if(!empty($conditions) && is_array($conditions)){
            $sql .= ' WHERE ';
            $i = 0;
            foreach($conditions as $key => $value){
                $pre = ($i > 0)?' AND ':'';
                $sql .= $pre.$key." = '".$this->escapeString($value)."'";
                $i++;
            }
        }
        elseif(!empty($conditions) && !is_array($conditions)){
            $sql .= ' WHERE '.$conditions;
        }
        else{
            //nothing
        }

        $query_result = $this->db->query($sql) or die($this->db->error);
		while($row = $query_result->fetch_assoc()) {
			$result_array[] = $row;
		}
		return !empty($query_result->num_rows > 0) ? $result_array : false;
    }

    /*
     * SELECT and return one record from a table, or false if no record found
     * @param table is the name of the database table
     * @param comma_sep_fields is a comma-separated list of fields to select
     * @param array or string conditions: array of SELECT clauses or a comma-separated list of clauses
     * Example $db->showOneRecords("users","name,phone,address,city,country",array('city'=>'Lagos',"country"=>"Nigeria"))
     * Returns associative array of the result. Use var_dump(result) to check array
    */
    public function showOneRecord($table,$comma_sep_fields="*",$conditions="")
    {
        $this->tblName = $this->tbl_prefix.$table;
        $sql = "SELECT ".$comma_sep_fields." FROM ".$this->tblName;
        if(!empty($conditions) && is_array($conditions)){
            $sql .= ' WHERE ';
            $i = 0;
            foreach($conditions as $key => $value){
                $pre = ($i > 0)?' AND ':'';
                $sql .= $pre.$key." = '".$this->escapeString($value)."'";
                $i++;
            }
        }
        elseif(!empty($conditions) && !is_array($conditions)){
            $sql .= ' WHERE '.$conditions;
        }
        else{
            //nothing
        }
        $query_result = $this->db->query($sql) or die($this->db->error);
        return !empty($query_result->num_rows > 0) ? $query_result->fetch_assoc() : false;
    }

    /*
     * Returns int number of rows from the database based on the given conditions, or false if no record found
     * @param string table: name of the table
     * @param array or string conditions: conditions is an array of SELECT clauses or a comma-separated list of clauses
     * Example $db->numRows("users","name,phone,address,city,country",array('city'=>'Lagos',"country"=>"Nigeria"))
    */
    */
    public function numRows($table,$conditions = array('')){
        $this->tblName = $this->tbl_prefix.$table;
        $sql = 'SELECT * FROM '.$this->tblName;
        if(!empty($conditions) && is_array($conditions)){
            $sql .= ' WHERE ';
            $i = 0;
            foreach($conditions as $key => $value){
                $pre = ($i > 0)?' AND ':'';
                $sql .= $pre.$key." = '".$this->escapeString($value)."'";
                $i++;
            }
        }
        elseif(!empty($conditions) && !is_array($conditions)){
            $sql .= ' WHERE '.$conditions;
        }
        else{
            //nothing
        }
        $result = $this->db->query($sql) or die($this->db->error);
        return $result->num_rows;
    }

    /*
     * Insert data into the database
     * @param string table: name of the table
     * @param data_array: the data to insert into the table
     */
    public function insertRecord($table,$data_array){
        $this->tblName = $this->tbl_prefix.$table;
        if(!empty($data_array) && is_array($data_array)){
            $columns = '';
            $values  = '';
            $i = 0;
            foreach($data_array as $key=>$val){
                $pre = ($i > 0)?', ':'';
                $columns .= $pre.$key;
                $values  .= $pre."'".$this->escapeString($val)."'";
                $i++;
            }
            $query = "INSERT INTO ".$this->tblName." (".$columns.") VALUES (".$values.")";
            $insert = $this->db->query($query) or die($this->db->error);
            return $insert ? $this->db->insert_id : false;
        }else{
            return false;
        }
    }

    /*
     * Update data into the database
     * @param string table: name of the table
     * @param array data_array: the data to update in the table
     * @param array or string conditions: WHERE condition on data update
    */
    public function updateRecord($table,$data_array,$conditions){
        $this->tblName = $this->tbl_prefix.$table;
        if(!empty($data_array) && is_array($data_array)){
            $colvalSet = '';
            $whereSql = '';
            $i = 0;
            foreach($data_array as $key=>$val){
                $pre = ($i > 0)?', ':'';
                $colvalSet .= $pre.$key."='".$this->escapeString($val)."'";
                $i++;
            }
            if(!empty($conditions) && is_array($conditions)){
                $whereSql .= ' WHERE ';
                $i = 0;
                foreach($conditions as $key => $value){
                    $pre = ($i > 0)?' AND ':'';
                    $whereSql .= $pre.$key." = '".$this->escapeString($value)."'";
                    $i++;
                }
            }
            elseif(!empty($conditions) && !is_array($conditions)){
                $whereSql .= ' WHERE '.$conditions;
            }
            else{
                //nothing
            }
            $query = "UPDATE ".$this->tblName." SET ".$colvalSet.$whereSql;
            $update = $this->db->query($query) or die($this->db->error);
            return $update ? $this->db->affected_rows : false;
        }else{
            return false;
        }
    }

    /*
     * Delete record(s) from database
     * @param string table: name of the table
     * @param array or string conditions: WHERE condition on data to delete
    */
    public function deleteRecord($table,$conditions){
        $this->tblName = $this->tbl_prefix.$table;
        if(!empty($conditions)){
            $whereSql = '';
            $i = 0;
            if(is_array($conditions)){
                $whereSql .= ' WHERE ';
                $i = 0;
                foreach($conditions as $key => $value){
                    $pre = ($i > 0)?' AND ':'';
                    $whereSql .= $pre.$key." = '".$this->escapeString($value)."'";
                    $i++;
                }
            }
            elseif(!is_array($conditions)){
                $whereSql .= ' WHERE '.$conditions;
            }
            else{
                //nothing
            }

            $query = "DELETE FROM ".$this->tblName.$whereSql;
            $delete = $this->db->query($query) or die($this->db->error);
            return $delete ? $this->db->affected_rows : false;
        }else{
            return false;
        }
    }

    /*
        * Close database connection
    */
    public function dbClose()
    {
        $this->db->close();
    }
}
?>