<?php

require_once 'Service.php';
require_once __DIR__ . '/../repository/GroupRepository.php';
require_once __DIR__ . '/../repository/UserRepository.php';

class GroupService extends Service
{
    private $groupRepository;
    private $userRepository;

    public function __construct()
    {
        $this->groupRepository = new GroupRepository();
        $this->userRepository = UserRepository::getInstance();
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
            $group['balance'] = rand(-100, 100);
        }

        return $this->success($groups);
    }

    public function getFullGroupDetails(int $groupId): array
    {
        if (!$this->getCurrentUserId()) return $this->error("Nieautoryzowany", 401);

        $group = $this->groupRepository->getGroupDetails($groupId);
        if (!$group) return $this->error("Grupa nie istnieje", 404);

        $data = [
            'group' => $group,
            'members' => $this->groupRepository->getGroupMembers($groupId),
            'expenses' => $this->groupRepository->getGroupExpenses($groupId)
        ];

        foreach ($data['members'] as &$m) {
            $m['balance'] = rand(-50, 50);
        }

        return $this->success($data);
    }
}