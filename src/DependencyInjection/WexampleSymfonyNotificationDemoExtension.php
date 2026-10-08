<?php

namespace Wexample\SymfonyNotificationDemo\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Wexample\SymfonyHelpers\DependencyInjection\AbstractWexampleSymfonyExtension;
use Wexample\SymfonyNotification\Class\NotificationCategory;

class WexampleSymfonyNotificationDemoExtension extends AbstractWexampleSymfonyExtension
{
    private const string TEXTS = 'WexampleSymfonyNotificationDemoBundle.common.notifications::';

    /**
     * The categories of the demo, declared here so that installing the bundle
     * is all an application does: one per level, sent on request; a reminder;
     * and what the teams of symfony-user-demo tell their members.
     */
    public function prepend(ContainerBuilder $container): void
    {
        parent::prepend($container);

        $categories = [];

        foreach (NotificationCategory::LEVELS as $level) {
            $categories['demo.' . $level] = $this->category($level, 'demo.group.try', $level);
        }

        $categories['demo.reminder'] = $this->category('reminder', 'demo.group.try', NotificationCategory::LEVEL_WARNING);
        $categories['team.joined'] = $this->category('team_joined', 'demo.group.teams', NotificationCategory::LEVEL_SUCCESS, 'user_teams');
        $categories['team.grants_changed'] = $this->category('team_grants_changed', 'demo.group.teams', NotificationCategory::LEVEL_INFO, 'user_teams');
        $categories['team.left'] = $this->category('team_left', 'demo.group.teams', NotificationCategory::LEVEL_WARNING);
        $categories['team.member_joined'] = $this->category('team_member_joined', 'demo.group.teams', NotificationCategory::LEVEL_INFO, 'user_teams');

        $container->prependExtensionConfig('wexample_symfony_notification', ['categories' => $categories]);
    }

    public function load(
        array $configs,
        ContainerBuilder $container
    ): void {
        $this->loadConfig(
            __DIR__,
            $container
        );
    }

    private function category(string $key, string $group, string $level, ?string $route = null): array
    {
        return [
            'title' => self::TEXTS . $key . '.title',
            'body' => self::TEXTS . $key . '.body',
            'label' => self::TEXTS . $key . '.label',
            'group' => self::TEXTS . $group,
            'level' => $level,
            'channels' => ['mail'],
            'route' => $route,
        ];
    }
}
