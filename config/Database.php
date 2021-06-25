<?php
class Database
{
  // DB Parameters
  private $hostName = "HOST_NAME";
  private $dbname = "DATABASE_NAME";
  private $username = "YOUR_USERNAME";
  private $password = "YOUR_PASSWORD";
  private $pdo;

  // Start Connection
  public function __construct()
  {
    $this->pdo = null;
    try {
      $this->pdo = new PDO("mysql:host=$this->hostName;dbname=$this->dbname;", $this->username, $this->password);
      $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
      $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
      echo "Error : " . $e->getMessage();
    }
  }

  public function fetchAll($query)
  {
    $stmt = $this->pdo->prepare($query);
    $stmt->execute();
    $rowCount = $stmt->rowCount();
    if ($rowCount <= 0) {
      return 0;
    } else {
      return $stmt->fetchAll();
    }
  }

  public function fetchOne($query, $parameter)
  {
    $stmt = $this->pdo->prepare($query);
    $stmt->execute([$parameter]);
    $rowCount = $stmt->rowCount();
    if ($rowCount <= 0) {
      return 0;
    } else {
      return $stmt->fetch();
    }
  }

  public function executeCall($username, $calls_allowed, $timeOutSeconds)
  {
    $query = "SELECT plan, calls_made, time_start, time_end
              FROM users
              WHERE username = '$username'
      ";
    $stmt = $this->pdo->prepare($query);
    $stmt->execute([$username]);
    $results = $stmt->fetch();

    // VARIABLES NEEDED
    // IF IT IS TIMEOUT OR EQUAL TO ZEOR SET TO TRUE
    $timeOut = date(time()) - $results['time_start'] >= $timeOutSeconds || $results['time_start'] === 0;

    // UPDATE CALLS MADE WITH RESPECE TO TIME OUT
    $query = "UPDATE users 
      SET calls_made = ";
    $query .= $timeOut ? " 1, time_start = " . date(time()) . " , time_end = " . strtotime("+ $timeOutSeconds seconds") : " calls_made + 1";
    $query .= " WHERE username = ? ";

    // INSTEAD OF FETCHING AGAIN USING SELECT ALL UPDATE VARIABLES
    $results['calls_made'] = $timeOut ? 1 : $results['calls_made'] + 1;
    $results['time_end'] = $timeOut ? strtotime("+ $timeOutSeconds seconds") : $results['time_end'];

    // EXECUTE CODE WITH RESPECT TO PLANS
    if ($results['plan'] === "unlimited") {
      $stmt = $this->pdo->prepare($query);
      $stmt->execute([$username]);
      return $results;
    } else {
      // IF NO TIME OUT AND CALLS MADE IS GREATER THAN CALLS ALLOWED RETURN -1
      if ($timeOut === false && $results['calls_made'] >= $calls_allowed) {
        return -1;
      } else {
        // GRANT ACCESS
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$username]);
        return $results;
      }
    }
  }

  public function insertOne($query, $title, $user_id, $date_time, $todos)
  {
    $stmt = $this->pdo->prepare($query);
    $stmt->execute([$title, $user_id, $date_time, $todos]);
  }

  public function updateOne($query, $title, $todos, $id)
  {
    $stmt = $this->pdo->prepare($query);
    $stmt->execute([$title, $todos, $id]);
  }

  public function deleteOne($query, $id)
  {
    $stmt = $this->pdo->prepare($query);
    $stmt->execute([$id]);
  }
}
