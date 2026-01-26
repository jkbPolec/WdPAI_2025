<?php
require_once 'Repository.php';

class PaymentRepository extends Repository {
    public function getPaymentsByGroup(int $groupId): array {
        $stmt = $this->database->connect()->prepare('
            SELECT from_user, to_user, amount
            FROM group_payment
            WHERE group_id = :group_id
        ');

        $stmt->bindParam(':group_id', $groupId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createPayment(int $groupId, int $fromUser, int $toUser, float $amount): int {
        $stmt = $this->database->connect()->prepare('
            INSERT INTO group_payment (group_id, from_user, to_user, amount)
            VALUES (:group_id, :from_user, :to_user, :amount)
            RETURNING id
        ');

        $stmt->bindParam(':group_id', $groupId, PDO::PARAM_INT);
        $stmt->bindParam(':from_user', $fromUser, PDO::PARAM_INT);
        $stmt->bindParam(':to_user', $toUser, PDO::PARAM_INT);
        $stmt->bindParam(':amount', $amount);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['id'];
    }
}
