# symfony-notification-demo

Version: 2.0.12

Demo of symfony-notification: notifications sent on request, a reminder once per window, and the teams of symfony-user-demo telling their members

## Table of Contents

- [Installation](#installation)

## Installation

```php
// config/bundles.php
Wexample\SymfonyNotification\WexampleSymfonyNotificationBundle::class => ['all' => true],
Wexample\SymfonyNotificationDs\WexampleSymfonyNotificationDsBundle::class => ['all' => true],
Wexample\SymfonyNotificationDemo\WexampleSymfonyNotificationDemoBundle::class => ['all' => true],
```

The demo of symfony-notification, at `/notification-demo/`, on the accounts and teams of symfony-user-demo — it has no users of its own. It declares its categories itself: installing the bundle is all there is to do, beside the migration of symfony-notification's tables.

- One notification per level, sent to oneself on request.
- A reminder sent with `notifyOnce()`, at most once an hour.
- The teams of symfony-user-demo telling their members: added, access changed, removed, and on arrival the other administrators. Read from the security journal symfony-user writes each membership change to (`TeamNotificationSubscriber`), so the user demo knows nothing of notifications.
