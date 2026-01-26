<?php
require_once 'Service.php';
require_once __DIR__ . '/../repository/ExpenseRepository.php';
require_once __DIR__ . '/../repository/GroupRepository.php';

class ExpenseService extends Service {
    private $expenseRepository;
    private $groupService;

    public function __construct() {
        $this->expenseRepository = new ExpenseRepository();
        $this->groupRepository = new GroupRepository();
    }

    public function addExpense(array $data): array {
        $userId = $this->getCurrentUserId();
        $groupId = (int)$data['group_id'];
        $groupRepo = new GroupRepository(); 
        if (!$groupRepo->isUserInGroup($groupId, $userId)) {
            return $this->error("Nie masz uprawnień do dodawania wydatków w tej grupie.", 403);
        }
        $amount = (float)$data['amount'];
        $participants = $data['participants'] ?? [];

        if (!$userId) return $this->error("Brak autoryzacji", 401);
        if (empty($data['name']) || $amount <= 0 || empty($participants)) {
            return $this->error("Wszystkie pola (w tym uczestnicy) są wymagane.");
        }

        try {
            $expenseId = $this->expenseRepository->createExpense($groupId, $userId, $data['name'], $amount);

            foreach ($participants as $pUserId) {
                $this->expenseRepository->addExpenseUser($expenseId, (int)$pUserId);
            }

            return $this->success(['group_id' => $groupId], "Wydatek dodany pomyślnie.");
        } catch (Exception $e) {
            return $this->error("Błąd zapisu: " . $e->getMessage());
        }
    }
}