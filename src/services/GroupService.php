<?php

require_once 'Service.php';
require_once __DIR__ . '/../repository/GroupRepository.php';
require_once __DIR__ . '/../repository/UserRepository.php';
require_once __DIR__ . '/../repository/ExpenseRepository.php';
require_once __DIR__ . '/../repository/PaymentRepository.php';

class GroupService extends Service
{
    private $groupRepository;
    private $userRepository;
    private $expenseRepository;
    private $paymentRepository;

    public function __construct()
    {
        $this->groupRepository = new GroupRepository();
        $this->userRepository = UserRepository::getInstance();
        $this->expenseRepository = new ExpenseRepository();
        $this->paymentRepository = new PaymentRepository();
    }

    public function createGroup(array $data): array
    {
        $ownerId = $this->getCurrentUserId();
        if (!$ownerId) return $this->error("Użytkownik nie jest zalogowany", 401);

        if (!$this->validate($data, ['name'])) {
            return $this->error("Nazwa grupy jest wymagana");
        }

        try {
            $groupId = $this->groupRepository->createGroup($data['name'], $data['description'] ?? '', $ownerId);
            $this->groupRepository->addMember($groupId, $ownerId);

            $emails = json_decode($data['members'] ?? '[]', true);
            foreach ($emails as $email) {
                $user = $this->userRepository->getUserByEmail($email);
                if ($user) {
                    $this->groupRepository->addMember($groupId, $user['id']);
                }
            }

            return $this->success(['id' => $groupId], "Grupa została utworzona");
        } catch (Exception $e) {
            return $this->error("Błąd bazy danych: " . $e->getMessage(), 500);
        }
    }

    public function getUserGroups(): array
    {
        $userId = $this->getCurrentUserId();
        if (!$userId) return $this->error("Nieautoryzowany", 401);

        $groups = $this->groupRepository->getGroupsByUserId($userId);

        foreach ($groups as &$group) {
            $balanceData = $this->calculateGroupBalances((int)$group['id'], $userId);
            $group['balance'] = $balanceData['user_balance'];
        }

        return $this->success($groups);
    }

    public function getFullGroupDetails(int $groupId): array
    {
        $userId = $this->getCurrentUserId();
        if (!$userId) {
            return $this->error("Nieautoryzowany", 401);
        }

        if (!$this->groupRepository->isUserInGroup($groupId, $userId)) {
            return $this->error("Nie masz uprawnień do przeglądania tej grupy.", 403);
        }

        $group = $this->groupRepository->getGroupDetails($groupId);
        if (!$group) {
            return $this->error("Grupa nie istnieje", 404);
        }

        $balanceData = $this->calculateGroupBalances($groupId, $userId);
        $memberBalances = $balanceData['member_balances'];
        $allMembers = $this->groupRepository->getGroupMembers($groupId);
        $membersForDisplay = [];
        foreach ($allMembers as $member) {
            if ((int)$member['id'] === $userId) {
                continue;
            }
            $memberId = (int)$member['id'];
            $member['balance'] = $memberBalances[$memberId] ?? 0.0;
            $membersForDisplay[] = $member;
        }

        $data = [
            'group' => $group,
            'members' => $membersForDisplay,
            'all_members' => $allMembers,
            'expenses' => $this->groupRepository->getGroupExpenses($groupId)
        ];

        return $this->success($data);
    }

    public function canUserAccessGroup(int $groupId): bool {
        $userId = $this->getCurrentUserId();
        if (!$userId) return false;
        return $this->groupRepository->isUserInGroup($groupId, $userId);
    }

    private function calculateGroupBalances(int $groupId, int $userId): array
    {
        $members = $this->groupRepository->getGroupMembers($groupId);
        $balances = [];
        foreach ($members as $member) {
            $memberId = (int)$member['id'];
            if ($memberId === $userId) {
                continue;
            }
            $balances[$memberId] = 0.0;
        }

        $rows = $this->expenseRepository->getExpenseParticipantsByGroup($groupId);
        $expenses = [];
        foreach ($rows as $row) {
            $expenseId = (int)$row['expense_id'];
            if (!isset($expenses[$expenseId])) {
                $expenses[$expenseId] = [
                    'created_by' => (int)$row['created_by'],
                    'amount' => (float)$row['amount'],
                    'participants' => []
                ];
            }
            $expenses[$expenseId]['participants'][] = (int)$row['participant_id'];
        }

        foreach ($expenses as $expense) {
            $participants = $expense['participants'];
            $count = count($participants);
            if ($count === 0) {
                continue;
            }
            $share = $expense['amount'] / $count;
            $payerId = $expense['created_by'];

            if ($payerId === $userId) {
                foreach ($participants as $participantId) {
                    if ($participantId === $userId) {
                        continue;
                    }
                    if (!isset($balances[$participantId])) {
                        $balances[$participantId] = 0.0;
                    }
                    $balances[$participantId] += $share;
                }
                continue;
            }

            if (in_array($userId, $participants, true)) {
                if (!isset($balances[$payerId])) {
                    $balances[$payerId] = 0.0;
                }
                $balances[$payerId] -= $share;
            }
        }

        $payments = $this->paymentRepository->getPaymentsByGroup($groupId);
        foreach ($payments as $payment) {
            $fromUser = (int)$payment['from_user'];
            $toUser = (int)$payment['to_user'];
            $amount = (float)$payment['amount'];

            if ($fromUser === $userId && $toUser !== $userId) {
                if (!isset($balances[$toUser])) {
                    $balances[$toUser] = 0.0;
                }
                $balances[$toUser] += $amount;
                continue;
            }

            if ($toUser === $userId && $fromUser !== $userId) {
                if (!isset($balances[$fromUser])) {
                    $balances[$fromUser] = 0.0;
                }
                $balances[$fromUser] -= $amount;
            }
        }

        $userBalance = 0.0;
        foreach ($balances as $memberId => $balance) {
            $balances[$memberId] = round($balance, 2);
            $userBalance += $balances[$memberId];
        }

        return [
            'member_balances' => $balances,
            'user_balance' => round($userBalance, 2)
        ];
    }
}
