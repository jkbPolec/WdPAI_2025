<?php
require_once 'AppController.php';
require_once __DIR__ . '/../services/GroupService.php';
require_once __DIR__ . '/../repository/GroupRepository.php';

class GroupController extends AppController {
    private $groupService;
    private $groupRepository;

    public function __construct() {
        $this->groupService = new GroupService();
        $this->groupRepository = new GroupRepository();
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

     public function getGroups() {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(403);
            exit();
        }

        header('Content-type: application/json');
        http_response_code(200);

        $groups = $this->groupRepository->getGroupsByUserId($_SESSION['user_id']);
        
        foreach ($groups as &$group) {
            $group['balance'] = rand(-100, 100); 
        }

        echo json_encode($groups);
        exit();
    }
}