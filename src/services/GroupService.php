<?php
require_once __DIR__ . '/../repository/GroupRepository.php';
require_once __DIR__ . '/../repository/UserRepository.php';

class GroupService {
    private $groupRepository;
    private $userRepository;

    public function __construct() {
        $this->groupRepository = new GroupRepository();
        $this->userRepository = UserRepository::getInstance();
    }

    public function createGroupWithMembers(array $data, int $ownerId): bool {
        try {
            $groupId = $this->groupRepository->createGroup($data['name'], $data['description'] ?? '', $ownerId);
            $this->groupRepository->addMember($groupId, $ownerId);

            $emails = json_decode($data['members'], true) ?? [];
            foreach ($emails as $email) {
                $user = $this->userRepository->getUserByEmail($email);
                if ($user) {
                    $this->groupRepository->addMember($groupId, $user['id']);
                }
            }
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}