<?php

namespace Wexample\SymfonyNotificationDemo\Controller\Pages;

use DateTimeImmutable;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Wexample\SymfonyDesignSystem\Attribute\MenuItem;
use Wexample\SymfonyHelpers\Helper\VariableHelper;
use Wexample\SymfonyLoader\Controller\AbstractPagesController;
use Wexample\SymfonyNotification\Class\NotificationCategory;
use Wexample\SymfonyNotification\Service\NotificationService;
use Wexample\SymfonyNotificationDemo\Traits\SymfonyNotificationDemoBundleClassTrait;
use Wexample\SymfonyUserDemo\Controller\Pages\UserController;

/**
 * Notifications sent to oneself on request, one per level, and a reminder
 * sent at most once an hour — beside what the teams of the user demo send
 * on their own.
 */
#[Route(path: '/notification-demo/', name: 'notification_demo_')]
final class NotificationDemoController extends AbstractPagesController
{
    use SymfonyNotificationDemoBundleClassTrait;

    final public const string ROUTE_INDEX = VariableHelper::INDEX;

    /** The application's form token id, declared stateless already. */
    private const string CSRF_TOKEN_ID = 'submit';

    #[Route(path: '', name: self::ROUTE_INDEX)]
    // A user's notifications, under the user's menu entry.
    #[MenuItem(UserController::MENU_GROUP, 3)]
    public function index(Request $request): Response
    {
        return $this->renderPage(self::ROUTE_INDEX, [
            'levels' => NotificationCategory::LEVELS,
            'csrf_token_id' => self::CSRF_TOKEN_ID,
            'sent' => $request->query->get('sent'),
        ]);
    }

    #[Route(path: 'send/{level}', name: 'send', requirements: ['level' => 'info|success|warning|danger|reminder'], methods: [Request::METHOD_POST])]
    public function send(string $level, Request $request, NotificationService $notifications): RedirectResponse
    {
        $user = $this->getUser();

        if (! $user || ! $this->isCsrfTokenValid(self::CSRF_TOKEN_ID, (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }

        $sent = $level === 'reminder'
            ? $notifications->notifyOnce('demo.reminder', [$user], null, new DateTimeImmutable('-1 hour'))
            : $notifications->notify('demo.' . $level, [$user]);

        return $this->redirectToRoute('notification_demo_index', ['sent' => $sent ? $level : 'none']);
    }
}
