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

    public function getGroupDetails(int $groupId): ?array {
        $stmt = $this->database->connect()->prepare('SELECT * FROM "group" WHERE id = ?');
        $stmt->execute([$groupId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
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
}