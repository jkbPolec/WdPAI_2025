<?php
require_once 'AppController.php';
require_once __DIR__ . '/../services/GroupService.php';

class GroupController extends AppController {
    private $groupService;

    public function __construct() {
        $this->groupService = new GroupService();
    }

    public function addGroup() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: /login");
            exit();
        }

        if (!$this->isPost()) {
            return $this->render('addGroup');
        }

        if ($this->groupService->createGroupWithMembers($_POST, $_SESSION['user_id'])) {
            header("Location: /dashboard");
        } else {
            return $this->render('addGroup', ['messages' => 'Wystąpił błąd podczas tworzenia grupy.']);
        }
    }
}