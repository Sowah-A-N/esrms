<?php
include(__DIR__ . '/db_connect.php');

class DBSessionHandler
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
        session_set_save_handler(
            array($this, 'open'),
            array($this, 'close'),
            array($this, 'read'),
            array($this, 'write'),
            array($this, 'destroy'),
            array($this, 'gc')
        );
        register_shutdown_function('session_write_close');
    }

    public function open($save_path, $session_name)
    {
        return true;
    }

    public function close()
    {
        return true;
    }

    public function read($id)
    {
        $id = mysqli_real_escape_string($this->conn, $id);
        $sql = "SELECT session_data FROM login_sessions WHERE session_id='$id' LIMIT 1";
        $result = mysqli_query($this->conn, $sql);
        if ($result && $row = mysqli_fetch_assoc($result)) {
            return $row['session_data'];
        }
        return '';
    }

   public function write($id, $data)
    {
        $id = mysqli_real_escape_string($this->conn, $id);
        $data = mysqli_real_escape_string($this->conn, $data);
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $agent = mysqli_real_escape_string($this->conn, $_SERVER['HTTP_USER_AGENT'] ?? '');
        $user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 'NULL';

        $sql = "REPLACE INTO login_sessions (session_id, user_id, ip_address, user_agent, session_data, last_activity)
                VALUES ('$id', $user_id, '$ip', '$agent', '$data', NOW())";
        mysqli_query($this->conn, $sql);
        return true;
    }


    public function destroy($id)
    {
        $id = mysqli_real_escape_string($this->conn, $id);
        $sql = "DELETE FROM login_sessions WHERE session_id='$id'";
        mysqli_query($this->conn, $sql);
        return true;
    }

    public function gc($maxlifetime)
    {
        $sql = "DELETE FROM login_sessions WHERE last_activity < (NOW() - INTERVAL $maxlifetime SECOND)";
        mysqli_query($this->conn, $sql);
        return true;
    }

}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

new DBSessionHandler($conn);

?>