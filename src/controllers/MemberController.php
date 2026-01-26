<?php

require_once 'AppController.php';
require_once __DIR__ . '/../services/MemberService.php';

class MemberController extends AppController
{
    private $memberService;

    public function __construct()
    {
        $this->memberService = new MemberService();
    }

    public function addMember()
    {
        if (!$this->isPost()) {
            http_response_code(405);
            exit();
        }

        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            $this->sendJsonResponse(['status' => 'error', 'message' => 'Brak autoryzacji', 'code' => 401]);
        }

        $groupId = (int)($_POST['group_id'] ?? 0);
        $email = trim($_POST['email'] ?? '');
        if ($groupId <= 0 || $email === '') {
            $this->sendJsonResponse(['status' => 'error', 'message' => 'Wszystkie pola są wymagane.', 'code' => 400]);
        }

        $result = $this->memberService->addMemberByEmail($groupId, $email, (int)$userId);
        $this->sendJsonResponse($result);
    }

    public function removeMember()
    {
        if (!$this->isPost()) {
            http_response_code(405);
            exit();
        }

        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            $this->sendJsonResponse(['status' => 'error', 'message' => 'Brak autoryzacji', 'code' => 401]);
        }

        $groupId = (int)($_POST['group_id'] ?? 0);
        $memberId = (int)($_POST['member_id'] ?? 0);
        if ($groupId <= 0 || $memberId <= 0) {
            $this->sendJsonResponse(['status' => 'error', 'message' => 'Wszystkie pola są wymagane.', 'code' => 400]);
        }

        $result = $this->memberService->removeMember($groupId, $memberId, (int)$userId);
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
