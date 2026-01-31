<?php

require_once 'AppController.php';
require_once __DIR__ . '/../services/GroupService.php';

class GroupController extends AppController
{
    private $groupService;

    public function __construct()
    {
        $this->groupService = new GroupService();
    }

    public function addGroup()
    {
        if ($this->isGet()) {
            return $this->render('addGroup');
        }

        $dto = new CreateGroupDTO(
            $_POST['name'] ?? '',
            $_POST['description'] ?? '',
            $_POST['members'] ?? '[]'
        );

        $result = $this->groupService->createGroup($dto);

        if ($result['status'] === 'success') {
            header("Location: /dashboard");
        } else {
            return $this->render('addGroup', ['messages' => $result['message']]);
        }
    }

    public function getGroups()
    {
        $result = $this->groupService->getUserGroups();
        $this->sendJsonResponse($result);
    }

    public function group()
    {
        if (!isset($_SESSION['user_id'])) {
        header("Location: /login");
        exit();
        }

        $groupId = (int)($_GET['id'] ?? 0);

        if (!$this->groupService->canUserAccessGroup($groupId)) {
            header("Location: /dashboard");
            exit();
        }

        $this->render('group');
    }

    public function getGroupDetails()
    {
        $groupId = (int)($_GET['id'] ?? 0);
        $result = $this->groupService->getFullGroupDetails($groupId);
        $this->sendJsonResponse($result);
    }

    private function sendJsonResponse(array $result)
    {
        header('Content-type: application/json');
        if (isset($result['code'])) {
            http_response_code($result['code']);
        }
        echo json_encode($result);
        exit();
    }
}