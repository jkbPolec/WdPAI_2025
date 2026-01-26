<?php
require_once 'Repository.php';

class ExpenseRepository extends Repository {

    public function createExpense(int $groupId, int $createdBy, string $name, float $amount, string $category): int {
        $stmt = $this->database->connect()->prepare('
            INSERT INTO group_expense (group_id, created_by, name, amount, category)
            VALUES (:group_id, :created_by, :name, :amount, :category)
            RETURNING id
        ');

        $stmt->bindParam(':group_id', $groupId, PDO::PARAM_INT);
        $stmt->bindParam(':created_by', $createdBy, PDO::PARAM_INT);
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':amount', $amount);
        $stmt->bindParam(':category', $category, PDO::PARAM_STR);
        
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['id'];
    }

    public function addExpenseUser(int $expenseId, int $userId): void {
        $stmt = $this->database->connect()->prepare('
            INSERT INTO group_expense_user (expense_id, user_id)
            VALUES (:expense_id, :user_id)
        ');

        $stmt->bindParam(':expense_id', $expenseId, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function getExpenseParticipantsByGroup(int $groupId): array {
        $stmt = $this->database->connect()->prepare('
            SELECT ge.id AS expense_id,
                   ge.created_by,
                   ge.amount,
                   geu.user_id AS participant_id
            FROM group_expense ge
            JOIN group_expense_user geu ON geu.expense_id = ge.id
            WHERE ge.group_id = :group_id
        ');

        $stmt->bindParam(':group_id', $groupId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
