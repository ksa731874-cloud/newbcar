<?php
  /**
   *
   */

  class DB
  {

    private $host    = DB_HOST;
    private $user    = DB_USER;
    private $pass    = DB_PASSWORD;
    private $name    = DB_NAME;
    private $charset = DB_CHARSET;

    // Database handler
    private $dbh;

    // Catch any error
    private $error;

    // Hold the statement
    private $stmt;

    // Set options
    private $options = array(

      // Increase performance by checking to see if there is already an established connection to the database
      PDO::ATTR_PERSISTENT => true,

      // Throw an exception if an error occurs. This then allows you to handle the error gracefully.
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    );

    public function __construct()
    {
      // Set DSN = Database Source Name
      $dsn = 'mysql:host=' . $this->host . ';dbname=' . $this->name . ';charset=' . $this->charset;

      // Creat a new PDO instanace
      try {
        $this->dbh = new PDO($dsn, $this->user, $this->pass, $this->options);
      }
      // Catch any errors
      catch (PDOException $e) {
        $this->error = $e->getMessage();
      }

    }

    public function query($query)
    {
      $this->stmt = $this->dbh->prepare($query);
    }

    // $param : placeholder value that we will be using in our SQL statement
    // $value : the actual value that we want to bind to the placeholder
    // $type  : the datatype of the parameter
    public function bind($param, $value, $type = null)
    {
      if(is_null($type)) {
        switch(true) {
          case is_int($value):
            $type = PDO::PARAM_INT;
          break;
          case is_bool($value):
            $type = PDO::PARAM_BOOL;
          break;
          case is_null($value):
            $type = PDO::PARAM_NULL;
          break;
          default:
            $type = PDO::PARAM_STR;
          break;
        }
      }
      $this->stmt->bindValue($param, $value, $type);
    }

    public function execute()
    {
      return $this->stmt->execute();
    }

    public function fetch()
    {
      $this->execute();
      return $this->stmt->fetch(PDO::FETCH_OBJ);
    }

    public function fetchAll()
    {
      $this->execute();
      return $this->stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // returns the number of effected rows from the previous delete, update or insert statement
    public function rowCount()
    {
      return $this->stmt->rowCount();
    }

    // returns the last inserted Id as a string
    public function lastInsertId()
    {
      return $this->dbh->lastInsertId();
    }

    public function beginTransaction()
    {
      return $this->dbh->beginTransaction();
    }

    public function endTransaction()
    {
      return $this->dbh->commit();
    }

    public function cancelTransaction()
    {
      return $this->dbh->rollBack();
    }

    //dumps the the information that was contained in the Prepared Statement
    public function debugDumpParams()
    {
      return $this->stmt->debugDumpParams();
    }
  }


?>
