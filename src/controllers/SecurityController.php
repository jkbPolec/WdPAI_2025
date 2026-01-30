<?php

require_once 'AppController.php';
require_once __DIR__ . '/../services/SecurityService.php';

class SecurityController extends AppController
{
    private SecurityService $securityService;

    public function __construct()
    {
        $this->securityService = new SecurityService();
    }

    public function login()
    {
        if (!$this->isPost()) return $this->render('login');

        if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
            return $this->render('login', ['messages' => 'Błąd CSRF.']);
        }

        $result = $this->securityService->login($_POST);

        if ($result['status'] === 'success') {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $result['data']['id'];
            $_SESSION['user_email'] = $result['data']['email'];
            $_SESSION['user_firstname'] = $result['data']['firstname'];

            header("Location: http://{$_SERVER['HTTP_HOST']}/dashboard");
            exit();
        }

        return $this->render('login', ["error" => $result['message']]);
    }

    public function register()
    {
        if ($this->isGet()) return $this->render('register');

        if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
            return $this->render('register', ['error' => 'Błąd CSRF.']);
        }

        $result = $this->securityService->register($_POST);

        if ($result['status'] === 'success') {
            return $this->render('login', ["messages" => $result['message']]);
        }

        return $this->render('register', ["error" => $result['message']]);
    }

    public function logout()
    {
        session_unset();
        session_destroy();
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        header("Location: http://{$_SERVER['HTTP_HOST']}/login");
        exit();
    }
}