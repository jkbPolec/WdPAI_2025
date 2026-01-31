<?php
require_once 'Repository.php';
require_once __DIR__ . '/../models/Group.php';

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

    public function getGroupsByUserId(int $userId): array {
        $stmt = $this->database->connect()->prepare('
            SELECT g.* FROM "group" g
            JOIN group_user gu ON g.id = gu.group_id
            WHERE gu.user_id = :userId
        ');
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $groups = [];
        foreach ($rows as $row) {
            $groups[] = new Group($row['id'], $row['name'], $row['description'], $row['owner']);
        }
        return $groups;
    }

    public function getGroupDetails(int $groupId): ?Group {
        $stmt = $this->database->connect()->prepare('SELECT * FROM "group" WHERE id = ?');
        $stmt->execute([$groupId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) return null;
        return new Group($row['id'], $row['name'], $row['description'], $row['owner']);
    }

    public function getGroupMembers(int $groupId): array {
        $stmt = $this->database->connect()->prepare('
            SELECT u.id, u.firstname, u.lastname, u.email 
            FROM users u
            JOIN group_user gu ON u.id = gu.user_id
            WHERE gu.group_id = ?
        ');
        $stmt->execute([$groupId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getGroupExpenses(int $groupId): array {
        $stmt = $this->database->connect()->prepare('
            SELECT ge.*, u.firstname, u.lastname 
            FROM group_expense ge
            JOIN users u ON ge.created_by = u.id
            WHERE ge.group_id = ?
            ORDER BY ge.created_at DESC
        ');
        $stmt->execute([$groupId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function isUserInGroup(int $groupId, int $userId): bool {
    $stmt = $this->database->connect()->prepare('
        SELECT 1 FROM group_user 
        WHERE group_id = :groupId AND user_id = :userId
    ');
    $stmt->bindParam(':groupId', $groupId, PDO::PARAM_INT);
    $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetch() !== false;
    }
}
