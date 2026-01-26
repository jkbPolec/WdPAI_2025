<?php
require_once 'AppController.php';
require_once __DIR__ . '/../services/ExpenseService.php';
require_once __DIR__ . '/../services/GroupService.php';

class ExpenseController extends AppController {
    private $expenseService;
    private $groupRepository;

    public function __construct() {
        $this->expenseService = new ExpenseService();
        $this->groupService = new GroupService();
    }

    public function addExpense() {
        if ($this->isGet()) {
            $groupId = (int)($_GET['groupId'] ?? 0);

            if ($groupId === 0 || !$this->groupService->canUserAccessGroup($groupId)) {
                header("Location: /dashboard");
                exit();
            }

            return $this->render('addExpense');
        }

        $result = $this->expenseService->addExpense($_POST);
        
        if ($result['status'] === 'success') {
            header("Location: /group?id=" . $result['data']['group_id']);
        } else {
            return $this->render('addExpense', ['messages' => $result['message']]);
        }
    }
}