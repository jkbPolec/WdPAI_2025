<?php

require_once 'src/controllers/SecurityController.php';
require_once 'src/controllers/DashboardController.php';
require_once 'src/controllers/GroupController.php';
class Routing
{

  public static $routes = [
    'login' => [
      'controller' => 'SecurityController',
      'action' => 'login'
    ],
    'register' => [
      'controller' => 'SecurityController',
      'action' => 'register'
    ],
    'dashboard' => [
      'controller' => 'DashboardController',
      'action' => 'index'
    ],
    'search-cards' => [
      'controller' => 'DashboardController',
      'action' => 'search'
    ],
    'ping' => [
      'controller' => 'DashboardController',
      'action' => 'ping'
    ],
    'logout' => [
        'controller' => 'SecurityController',
        'action' => 'logout'
    ],
    'addGroup' => [
        'controller' => 'GroupController',
        'action' => 'addGroup'
      ],
      'getGroups' => [
        'controller' => 'GroupController',
        'action' => 'getGroups'
    ]
  ];

  public static function run(string $path)
  {
    switch ($path) {
      case 'dashboard':
      case 'login':
      case 'register':
      case 'search-cards':
      case 'ping':
      case 'logout':
      case 'addGroup':
      case 'getGroups':
        $controller = Routing::$routes[$path]['controller'];
        $action = Routing::$routes[$path]['action'];

        $controllerObj = new $controller;
        $controllerObj->$action();

        break;
      default:
        include 'public/views/404.html';
        break;
    }
  }
}
