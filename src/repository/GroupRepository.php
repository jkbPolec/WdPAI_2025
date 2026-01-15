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

    public function getGroupsByUserId(int $userId): array
{
    $stmt = $this->database->connect()->prepare('
        SELECT g.id, g.name, g.description
        FROM "group" g
        JOIN group_user gu ON g.id = gu.group_id
        WHERE gu.user_id = :userId
    ');
    $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
}