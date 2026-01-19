<?php

require_once 'AppController.php';
require_once __DIR__ . '/../repository/UserRepository.php';

class DashboardController extends AppController
{

  public function index()
  {
    if (!isset($_SESSION['user_id'])) {
        $url = "http://$_SERVER[HTTP_HOST]";
        header("Location: {$url}/login");
        exit();
    }

    return $this->render("dashboard");
  }

  public function ping()
{
    header('Content-Type: application/json');
    http_response_code(200);

    echo json_encode([
        'status' => 'ok',
        'time' => date('Y-m-d H:i:s'),
        'message' => 'Serwer WDPAI odpowiada!'
    ]);
    exit(); 
}
}
