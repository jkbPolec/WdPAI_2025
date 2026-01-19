<?php
require_once 'Service.php';
require_once __DIR__ . '/../repository/ExpenseRepository.php';

class ExpenseService extends Service {
    private $expenseRepository;

    public function __construct() {
        $this->expenseRepository = new ExpenseRepository();
    }

    public function addExpense(array $data): array {
        $userId = $this->getCurrentUserId();
        $groupId = (int)$data['group_id'];
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