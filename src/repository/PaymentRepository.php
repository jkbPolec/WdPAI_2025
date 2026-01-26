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
}
