<?php

namespace Wexample\SymfonyNotificationDemo\EventSubscriber;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Wexample\SymfonyNotification\Service\NotificationService;
use Wexample\SymfonyUser\Enum\SecurityEventType;
use Wexample\SymfonyUser\Event\SecurityEvent;
use Wexample\SymfonyUser\Service\MembershipService;
use Wexample\SymfonyUserDemo\Entity\DemoTeam;
use Wexample\SymfonyUserDemo\Entity\DemoUser;

/**
 * The teams of symfony-user-demo telling their members: the member added,
 * changed or removed is told, and on arrival the other administrators of
 * the team too. Read from the journal symfony-user writes each change to,
 * so nothing in the user demo knows of notifications.
 */
class TeamNotificationSubscriber implements EventSubscriberInterface
{
    private const array MEMBER_CATEGORIES = [
        'membership.added' => 'team.joined',
        'membership.grants_changed' => 'team.grants_changed',
        'membership.removed' => 'team.left',
    ];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly NotificationService $notifications,
        private readonly MembershipService $membershipService,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [SecurityEvent::class => 'onSecurityEvent'];
    }

    public function onSecurityEvent(SecurityEvent $event): void
    {
        $category = self::MEMBER_CATEGORIES[$event->type->value] ?? null;

        if (! $category) {
            return;
        }

        $team = $this->find(DemoTeam::class, $event->extra['group'] ?? null);
        $member = $this->find(DemoUser::class, $event->extra['member'] ?? null);

        if (! $team || ! $member) {
            return;
        }

        $actor = $this->entityManager->find(DemoUser::class, $event->extra['actor_id'] ?? '');
        $parameters = [
            'team' => $team->getName(),
            'member' => (string) $member->getEmail(),
            'grants' => $event->extra['grants_after'] ?? '',
            'actor' => $actor ? (string) $actor->getEmail() : '',
        ];

        $this->notifications->notify($category, [$member], $team, $parameters, $actor);

        if ($event->type === SecurityEventType::MEMBERSHIP_ADDED) {
            $this->notifications->notify('team.member_joined', $this->getAdministrators($team, [$member, $actor]), $team, $parameters, $actor);
        }
    }

    /**
     * The members holding `administrator` in $team, but $except.
     *
     * @param list<?object> $except
     *
     * @return list<DemoUser>
     */
    private function getAdministrators(DemoTeam $team, array $except): array
    {
        $administrators = [];

        foreach ($team->getMemberships() as $membership) {
            $user = $membership->getMember();

            if (! in_array($user, $except, true) && $this->membershipService->holds($user, 'administrator', $team)) {
                $administrators[] = $user;
            }
        }

        return $administrators;
    }

    /**
     * The entity a journal key names, `Class#id`, when it is of $class.
     *
     * @template T of object
     *
     * @param class-string<T> $class
     *
     * @return T|null
     */
    private function find(string $class, ?string $key): ?object
    {
        [$type, $id] = array_pad(explode('#', (string) $key, 2), 2, null);

        return $id && is_a($type, $class, true) ? $this->entityManager->find($class, $id) : null;
    }
}
