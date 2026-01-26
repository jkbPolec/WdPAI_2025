<?php

require_once 'Repository.php';

class MemberRepository extends Repository
{
    public function getUserByEmail(string $email): ?array
    {
        $stmt = $this->database->connect()->prepare('
            SELECT id, firstname, lastname, email
            FROM users
            WHERE email = :email
        ');
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getUserById(int $userId): ?array
    {
        $stmt = $this->database->connect()->prepare('
            SELECT id, firstname, lastname, email
            FROM users
            WHERE id = :id
        ');
        $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function addMemberToGroup(int $groupId, int $userId): void
    {
        $stmt = $this->database->connect()->prepare('
            INSERT INTO group_user (group_id, user_id)
            VALUES (:group_id, :user_id)
            ON CONFLICT DO NOTHING
        ');
        $stmt->bindParam(':group_id', $groupId, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function removeMemberFromGroup(int $groupId, int $userId): void
    {
        $stmt = $this->database->connect()->prepare('
            DELETE FROM group_user
            WHERE group_id = :group_id AND user_id = :user_id
        ');
        $stmt->bindParam(':group_id', $groupId, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function isUserInGroup(int $groupId, int $userId): bool
    {
        $stmt = $this->database->connect()->prepare('
            SELECT 1 FROM group_user
            WHERE group_id = :group_id AND user_id = :user_id
        ');
        $stmt->bindParam(':group_id', $groupId, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch() !== false;
    }
}
