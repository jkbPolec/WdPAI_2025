<?php
require_once 'AppController.php';
require_once __DIR__ . '/../services/ExpenseService.php';

class ExpenseController extends AppController {
    private $expenseService;

    public function __construct() {
        $this->expenseService = new ExpenseService();
    }

    public function addExpense() {
        if (!$this->isPost()) {
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