<?php

//  .env
require_once "config.php";

//singleton
class Database
{
  private $username;
  private $password;
  private $host;
  //private $conn;
  private $database;

  public function __construct()
  {
    $this->username = USERNAME;
    $this->password = PASSWORD;
    $this->host = HOST;
    $this->database = DATABASE;
  }

  public function connect()
  {
    try {
      $conn = new PDO(
        "pgsql:host=$this->host;port=5432;dbname=$this->database",
        $this->username,
        $this->password,
        ["sslmode"  => "prefer"]
      );

      // set the PDO error mode to exception
      $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      return $conn;
    } catch (PDOException $e) {
      if ($this->isMissingDatabaseError($e)) {
        $this->bootstrapDatabase();
        return $this->connect();
      }
      die("Connection failed: " . $e->getMessage());
    }
  }

  public function disconnect($conn)
  {
    //this->conn = null;
  }

  private function isMissingDatabaseError(PDOException $e): bool
  {
    $message = $e->getMessage();
    return strpos($message, 'does not exist') !== false || strpos($message, '3D000') !== false;
  }

  private function bootstrapDatabase(): void
  {
    $adminConn = new PDO(
      "pgsql:host=$this->host;port=5432;dbname=postgres",
      $this->username,
      $this->password,
      ["sslmode"  => "prefer"]
    );
    $adminConn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $dbName = preg_replace('/[^a-zA-Z0-9_]/', '', $this->database);
    $adminConn->exec("CREATE DATABASE \"{$dbName}\"");

    $conn = new PDO(
      "pgsql:host=$this->host;port=5432;dbname=$this->database",
      $this->username,
      $this->password,
      ["sslmode"  => "prefer"]
    );
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $migrationFiles = glob(__DIR__ . '/src/db/migrations/V*.sql');
    sort($migrationFiles, SORT_STRING);
    foreach ($migrationFiles as $file) {
      $sql = file_get_contents($file);
      if ($sql !== false) {
        $conn->exec($sql);
      }
    }

    $seedFile = __DIR__ . '/src/db/seed.sql';
    if (file_exists($seedFile)) {
      $seedSql = file_get_contents($seedFile);
      if ($seedSql !== false) {
        $conn->exec($seedSql);
      }
    }
  }
}
