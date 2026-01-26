<?php

require_once 'Service.php';
require_once __DIR__ . '/../repository/MemberRepository.php';
require_once __DIR__ . '/../repository/GroupRepository.php';

class MemberService extends Service
{
    private $memberRepository;
    private $groupRepository;

    public function __construct()
    {
        $this->memberRepository = new MemberRepository();
        $this->groupRepository = new GroupRepository();
    }

    public function addMemberByEmail(int $groupId, string $email, int $requesterId): array
    {
        $group = $this->groupRepository->getGroupDetails($groupId);
        if (!$group) return $this->error("Grupa nie istnieje", 404);
        if ((int)$group['owner'] !== $requesterId) {
            return $this->error("Tylko właściciel grupy może zapraszać użytkowników.", 403);
        }

        $user = $this->memberRepository->getUserByEmail($email);
        if (!$user) return $this->error("Nie znaleziono użytkownika z tym adresem e-mail.");

        try {
            $this->memberRepository->addMemberToGroup($groupId, (int)$user['id']);
            return $this->success($user, "Użytkownik został dodany.");
        } catch (Exception $e) {
            return $this->error("Błąd zapisu członka.", 500);
        }
    }

    public function removeMember(int $groupId, int $memberId, int $requesterId): array
    {
        $group = $this->groupRepository->getGroupDetails($groupId);
        if (!$group) return $this->error("Grupa nie istnieje", 404);
        if ((int)$group['owner'] !== $requesterId) {
            return $this->error("Tylko właściciel grupy może usuwać użytkowników.", 403);
        }
        if ((int)$group['owner'] === $memberId) {
            return $this->error("Nie można usunąć właściciela grupy.");
        }

        if (!$this->memberRepository->isUserInGroup($groupId, $memberId)) {
            return $this->error("Użytkownik nie należy do tej grupy.", 404);
        }

        try {
            $this->memberRepository->removeMemberFromGroup($groupId, $memberId);
            return $this->success([], "Użytkownik został usunięty.");
        } catch (Exception $e) {
            return $this->error("Błąd usuwania członka.", 500);
        }
    }
}
