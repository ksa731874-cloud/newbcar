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
      // Get environment variables - MUST use Railway injected values
      // Check multiple possible env var names that Railway might use
      $this->host = $this->getEnv('DB_HOST', 'MYSQLHOST', 'MYSQL_HOST');
      $this->user = $this->getEnv('DB_USER', 'MYSQLUSER', 'MYSQL_USER');
      $this->pass = $this->getEnv('DB_PASSWORD', 'MYSQLPASSWORD', 'MYSQL_PASSWORD');
      $this->name = $this->getEnv('DB_NAME', 'MYSQL_DATABASE', 'MYSQLDB');
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
        $allEnvs = '';
        foreach (['DB_HOST', 'DB_USER', 'DB_PASSWORD', 'DB_NAME', 'MYSQLHOST', 'MYSQL_USER', 'MYSQL_PASSWORD', 'MYSQL_DATABASE'] as $envName) {
          $val = getenv($envName);
          $allEnvs .= "<p><strong>$envName:</strong> <code style='color: red;'>" . ($val ? $val : 'NOT SET') . "</code></p>";
        }
        die("
        <!DOCTYPE html>
        <html><head><title>Database Error</title>
        <style>
            body { font-family: Arial, sans-serif; padding: 40px; background: #f5f5f5; }
            .error-box { background: white; padding: 30px; border-radius: 10px; max-width: 800px; margin: 0 auto; border-left: 5px solid #e74c3c; }
            h1 { color: #e74c3c; margin-top: 0; }
            h3 { margin-top: 20px; color: #333; }
            .detail { background: #f8f8f8; padding: 15px; border-radius: 5px; margin-top: 15px; }
            code { background: #eee; padding: 2px 5px; border-radius: 3px; }
            .highlight { color: #e74c3c; font-weight: bold; }
        </style>
        </head>
        <body>
        <div class='error-box'>
            <h1>⚠️ Database Connection Error</h1>
            <p><strong>Message:</strong> <span class='highlight'>" . htmlspecialchars($e->getMessage()) . "</span></p>
            <h3>Values Being Used:</h3>
            <div class='detail'>
                <p><strong>Host:</strong> <code class='highlight'>" . htmlspecialchars($this->host ?: 'NULL') . "</code></p>
                <p><strong>Database:</strong> <code class='highlight'>" . htmlspecialchars($this->name ?: 'NULL') . "</code></p>
                <p><strong>User:</strong> <code class='highlight'>" . htmlspecialchars($this->user ?: 'NULL') . "</code></p>
                <p><strong>Password:</strong> <code class='highlight'>" . (empty($this->pass) ? '<em>(empty)</em>' : '********') . "</code></p>
            </div>
            <h3>All Environment Variables:</h3>
            <div class='detail'>
                $allEnvs
            </div>
            <p style='color: #666; margin-top: 20px;'>Please ensure Railway environment variables are correctly set in project settings.</p>
        </div>
        </body></html>
        ");
      }

    }
    
    /**
     * Get environment variable with multiple possible names
     */
    private function getEnv(...$names) {
      foreach ($names as $name) {
        $value = getenv($name);
        if ($value !== false && $value !== '') {
          return $value;
        }
        if (isset($_ENV[$name]) && $_ENV[$name] !== '') {
          return $_ENV[$name];
        }
      }
      return null;
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
