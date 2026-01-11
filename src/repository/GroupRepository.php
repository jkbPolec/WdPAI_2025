<?php
require_once 'Repository.php';

class GroupRepository extends Repository {

    public function createGroup(string $name, string $description, int $ownerId): int {
        $stmt = $this->database->connect()->prepare('
            INSERT INTO "group" (name, description, owner, status)
            VALUES (?, ?, ?, ?) RETURNING id
        ');

        $stmt->execute([
            $name,
            $description,
            $ownerId,
            'active'
        ]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['id'];
    }

    public function addMember(int $groupId, int $userId): void {
        $stmt = $this->database->connect()->prepare('
            INSERT INTO group_user (group_id, user_id)
            VALUES (?, ?)
        ');

        $stmt->execute([$groupId, $userId]);
    }
}