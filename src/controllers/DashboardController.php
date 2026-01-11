<?php

require_once 'AppController.php';
require_once __DIR__ . '/../repository/UserRepository.php';
require_once __DIR__ . '/../repository/CardRepository.php';

class DashboardController extends AppController
{

  private $cardRepository;

  public function __construct()
  {
    $this->cardRepository = new CardRepository();
  }

  public function index()
  {

    if (!isset($_SESSION['user_id'])) {
        $url = "http://$_SERVER[HTTP_HOST]";
        header("Location: {$url}/login");
        exit();
    }

    return $this->render("dashboard");
  }

  public function search()
  {

    $contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';

    if ($contentType !== "application/json") {
      http_response_code(415);
      echo json_encode(["error" => "Media type not supported"]);
      return;
    }

    $content = trim(file_get_contents("php://input"));
    $decoded = json_decode($content, true);



    header('Content-Type: application/json');
    http_response_code(200);

    echo json_encode($this->cardRepository->getCardsByTitle($decoded['search']));
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
