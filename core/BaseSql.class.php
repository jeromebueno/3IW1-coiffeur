<?php
/**
 * Created by PhpStorm.
 * User: antoine
 * Date: 25/03/2018
 * Time: 14:16
 */

class BaseSql{

    protected $db;
    private $table;
    private $columns;
    private static $_instance;

    /**
     * Tab for prepare statement
     */
    private $tab_st;



    public function __construct(){
        try{
            $this->db = new PDO(DBDRIVER.":host=".DBHOST.";dbname=".DBNAME , DBUSER, DBPWD);
        }catch(Exception $e){
            die("Erreur SQL :".$e->getMessage());
        }
        //$this->db = new Database();

        $this->table = strtolower(get_called_class());
    }

    public static function getInstance() {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function setColumns(){


        $this->columns = array_diff_key(
            get_object_vars( $this ),
            get_class_vars( get_class() )
        );
    }

   public function update( $statement, $params ){

        $query = $this->db->prepare( $statement );
        //print_r( $params ); die;
        $query->execute( $params );
    }


    public function save(){

        $this->setColumns();

        if( $this->id ){
            //UPDATE
            foreach ($this->columns as $key => $value) {
                $sqlSet[] = $key."=:".$key;
            }

            $query = $this->db->prepare(" UPDATE ".$this->table." SET ".implode(",", $sqlSet)." WHERE id=:id ");

            $query->execute($this->columns);


        }else{
            //INSERT
            unset($this->columns['id']);

            $query = $this->db->prepare("
					INSERT INTO ".$this->table." 
					(". implode(",", array_keys($this->columns)) .")
					VALUES
					(:". implode(",:", array_keys($this->columns)) .")
				");
            print_r ( $this->columns );
            $query->execute($this->columns);

        }

    }

    public function select( $sql, $params ){
        $query = $this->db->prepare( $sql );
        $query->execute( $params );
        $res = $query->fetch( PDO::FETCH_ASSOC );
        return $res;
    }


    public function generateToken( $email ){
       $token = substr(sha1("GDQgfds4354".$email.substr(time(), 5).uniqid()."gdsfd"), 2, 10);

       return $token;
    }


    /*
     * Function bindParams
     */

    public static function bindParams($params, $params_remove=array()) {
        $result = array();
        if(empty($params)) {
            throw new PDOException('Empty array for binding');
        }
        $result['fields'] = implode(',', array_keys($params));
        $tmp1 = array();
        $tmp2 = array();
        $tmp3 = array();
        foreach($params as $key => $value) {
            $tmp1[] = ':'.$key;
            $tmp2[] = $key.'=:'.$key;
            if(!in_array($key, $params_remove)) {
                $tmp3[] = $key.'=:'.$key;
            }
        }
        $result['bind_insert'] = implode(',', $tmp1);
        $result['bind_update'] = implode(',', $tmp2);
        $result['bind_onduplicate'] = implode(',', $tmp3);
        $result['bind_primary_key'] = implode(' AND ', $tmp3);
        return $result;
    }


    public function beginTransaction() {
        $this->db->beginTransaction();
    }
    /**
     *
     */
    public function commit() {
        $this->db->commit();
    }
    /**
     *
     */
    public function rollback() {
        $this->db->rollback();
    }


    /**
     *
     */
    private function fetch($sql, $params, $type, $index_by = null) {
        $result = false;
        $time = microtime(true);
        //$this->history($sql, $params);
        $st = $this->db->prepare($sql);
        $res = $st->execute($params);

        if('fetch_row'===$type) {
            $result = $st->fetch();
        } else if('fetch_one'===$type) {
            $result = $st->fetchColumn();
        } else {
            $tmp = $st->fetchAll();
            if(!empty($index_by) && isset($tmp[0][$index_by])) {
                $result = array();
                for($i=0; isset($tmp[$i]); $i++) {
                    $index = $tmp[$i][$index_by];
                    if(!isset($result[$index])) {
                        $result[$index] = array();
                    }
                    $result[$index][] = $tmp[$i];
                }
            } else {
                $result = $tmp;
            }
        }
        return $result;
    }

    /**
     *
     */
    public function fetchAll($sql, $params=null, $index_by = null) {
        return $this->fetch($sql, $params, 'fetch_all', $index_by);
    }
    /**
     *
     */
    public function fetchRow($sql, $params=null) {
        return $this->fetch($sql, $params, 'fetch_row');
    }
    /**
     *
     */
    public function fetchOne($sql, $params=null) {
        return $this->fetch($sql, $params, 'fetch_one');
    }


    /**
     * Common function to insert or update row
     *
     * @param string $table The table name
     * @param array $fields Associative array with fields to update
     * @param array $fields_primary_key Associative array with fields primary key
     * @param array $options Associative array for options
     * - max_updates = Max updates : Default 1. To prevent accident when composite primary key and field omitted ;-)
     * - insert_only = no update
     * @return array
     * @throws Exception on Error
     */
    public function updateTable($table, $fields, $fields_primary_key, $options=array()) {
        $res = null;
        $table = basename($table);
        $options = array_change_key_case((array)$options, CASE_LOWER);

        $bind_pk = $this->bindParams($fields_primary_key);
        $bind = $this->bindParams($fields);

        $found = $this->countTable($table, $fields_primary_key);
        $sql_params = array_merge($fields, $fields_primary_key);


        $sql_upd = 'UPDATE '.$table.' SET '.$bind['bind_update'].' WHERE '.$bind_pk['bind_primary_key'];

        $this->update($sql_upd, $sql_params);

    }

    public function countTable($table, $fields_primary_key) {

        $table = basename($table);

        $bind_pk = $this->bindParams($fields_primary_key);
        $sql_count = 'SELECT COUNT(*) FROM '.$table.' WHERE '.$bind_pk['bind_primary_key'];
        $found = $this->fetchOne($sql_count, $fields_primary_key);
        return $found;
    }



    public function populate($where = []){

        $sql = $this->db->prepare( "SELECT * FROM user WHERE email = :email" );
        //->fetchObject('User');
        $sql->execute( $where );
        $result = $sql->fetchObject('User');


        //return objet
        return $result;



    }

}