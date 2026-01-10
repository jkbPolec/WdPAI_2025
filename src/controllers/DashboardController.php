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
    // TODO prepare dataset, and display in HTML
    $cards = [
      [
        'id' => 1,
        'title' => 'Ace of Spades',
        'subtitle' => 'Legendary card',
        'imageUrlPath' => 'https://deckofcardsapi.com/static/img/AS.png',
        'href' => '/cards/ace-of-spades'
      ],
      [
        'id' => 2,
        'title' => 'Queen of Hearts',
        'subtitle' => 'Classic romance',
        'imageUrlPath' => 'https://deckofcardsapi.com/static/img/QH.png',
        'href' => '/cards/queen-of-hearts'
      ],
      [
        'id' => 3,
        'title' => 'King of Clubs',
        'subtitle' => 'Royal strength',
        'imageUrlPath' => 'https://deckofcardsapi.com/static/img/KC.png',
        'href' => '/cards/king-of-clubs'
      ],
      [
        'id' => 4,
        'title' => 'Jack of Diamonds',
        'subtitle' => 'Sly and sharp',
        'imageUrlPath' => 'https://deckofcardsapi.com/static/img/JD.png',
        'href' => '/cards/jack-of-diamonds'
      ],
      [
        'id' => 5,
        'title' => 'Ten of Hearts',
        'subtitle' => 'Lucky draw',
        'imageUrlPath' => 'https://deckofcardsapi.com/static/img/0H.png',
        'href' => '/cards/ten-of-hearts'
      ],
    ];


    $userRepository = new UserRepository();
    $users = $userRepository->getUsers();

    var_dump($users);

    return $this->render("dashboard", ['cards' => $cards]);
  }

  public function search()
  {

    $contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';

    if ($contentType !== "application/json") {
      http_response_code(415);
      echo json_encode(["error" => "Media type not supported"]);
      return;
    }

    if ($this->isPost()) {
      http_response_code(405);
      echo json_encode(["error" => "Method not allowed"]);
      return;
    }

    $content = trim(file_get_contents("php://input"));
    $decoded = json_decode($content, true);



    header('Content-Type: application/json');
    http_response_code(200);

    echo json_encode($this->cardRepository->getCardsByTitle($decoded['search']));
  }
}
