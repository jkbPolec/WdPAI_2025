<?php

require_once 'Service.php';
require_once __DIR__ . '/../repository/PaymentRepository.php';
require_once __DIR__ . '/../repository/GroupRepository.php';

class PaymentService extends Service
{
    private $paymentRepository;
    private $groupRepository;

    public function __construct()
    {
        $this->paymentRepository = new PaymentRepository();
        $this->groupRepository = new GroupRepository();
    }

    public function addPayment(array $data): array
    {
        $userId = $this->getCurrentUserId();
        if (!$userId) return $this->error("Brak autoryzacji", 401);

        $groupId = (int)($data['group_id'] ?? 0);
        $toUser = (int)($data['to_user'] ?? 0);
        $amount = (float)($data['amount'] ?? 0);

        if ($groupId <= 0 || $toUser <= 0 || $amount <= 0) {
            return $this->error("Wszystkie pola są wymagane.");
        }

        if ($userId === $toUser) {
            return $this->error("Nie można rozliczyć się z samym sobą.");
        }

        if (!$this->groupRepository->isUserInGroup($groupId, $userId) ||
            !$this->groupRepository->isUserInGroup($groupId, $toUser)) {
            return $this->error("Użytkownik nie należy do tej grupy.", 403);
        }

        try {
            $paymentId = $this->paymentRepository->createPayment($groupId, $userId, $toUser, $amount);
            return $this->success(['id' => $paymentId], "Płatność dodana.");
        } catch (Exception $e) {
            return $this->error("Błąd zapisu płatności.", 500);
        }
    }
}
