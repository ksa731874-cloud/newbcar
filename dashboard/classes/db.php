<?php
  /**
   * Database Connection Class
   * Supports environment variables for Railway deployment
   */

  class DB
  {

    private $host;
    private $user;
    private $pass;
    private $name;
    private $charset;

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
      // Get environment variables with Railway fallbacks
      $this->host = getenv('DB_HOST') ?: getenv('MYSQLHOST') ?: 'localhost';
      $this->user = getenv('DB_USER') ?: getenv('MYSQLUSER') ?: 'root';
      $this->pass = getenv('DB_PASSWORD') ?: getenv('MYSQLPASSWORD') ?: '';
      $this->name = getenv('DB_NAME') ?: getenv('MYSQL_DATABASE') ?: 'dalatew';
      $this->charset = defined('DB_CHARSET') ? DB_CHARSET : 'utf8mb4';
      
      // Set DSN = Database Source Name
      $dsn = 'mysql:host=' . $this->host . ';dbname=' . $this->name . ';charset=' . $this->charset;

      // Create a new PDO instance
      try {
        $this->dbh = new PDO($dsn, $this->user, $this->pass, $this->options);
      }
      // Catch any errors
      catch (PDOException $e) {
        $this->error = $e->getMessage();
        // Display error clearly for debugging
        die("
        <!DOCTYPE html>
        <html><head><title>Database Error</title>
        <style>
            body { font-family: Arial, sans-serif; padding: 40px; background: #f5f5f5; }
            .error-box { background: white; padding: 30px; border-radius: 10px; max-width: 600px; margin: 0 auto; border-left: 5px solid #e74c3c; }
            h1 { color: #e74c3c; margin-top: 0; }
            .detail { background: #f8f8f8; padding: 15px; border-radius: 5px; margin-top: 15px; }
            code { background: #eee; padding: 2px 5px; border-radius: 3px; }
        </style>
        </head>
        <body>
        <div class='error-box'>
            <h1>⚠️ Database Connection Error</h1>
            <p><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>
            <div class='detail'>
                <strong>Host:</strong> <code>" . htmlspecialchars($this->host) . "</code><br>
                <strong>Database:</strong> <code>" . htmlspecialchars($this->name) . "</code><br>
                <strong>User:</strong> <code>" . htmlspecialchars($this->user) . "</code>
            </div>
            <p style='color: #666; margin-top: 20px;'>Please check your Railway environment variables:</p>
            <code>DB_HOST, DB_USER, DB_PASSWORD, DB_NAME</code>
        </div>
        </body></html>
        ");
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
