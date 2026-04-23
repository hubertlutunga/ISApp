<?php

if (!defined('ISAPP_BOOTSTRAPPED')) {
    define('ISAPP_BOOTSTRAPPED', true);

    $isAppConfig = require __DIR__ . '/../config/app.php';
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../src/Support/EventUrlService.php';
    require_once __DIR__ . '/../src/Support/ShortUrlService.php';
    require_once __DIR__ . '/../src/Support/UserAccountService.php';
    require_once __DIR__ . '/../src/Support/MailerService.php';
    require_once __DIR__ . '/../src/Support/EventOrderService.php';
    require_once __DIR__ . '/../src/Support/EventCreationService.php';
    require_once __DIR__ . '/../src/Support/EventUpdateService.php';
    require_once __DIR__ . '/../src/Support/EventMediaService.php';
    require_once __DIR__ . '/../src/Support/EventDocumentService.php';
    require_once __DIR__ . '/../src/Support/MenuCatalogService.php';
    require_once __DIR__ . '/../src/Support/MenuOrderService.php';
    require_once __DIR__ . '/../src/Support/EventPrintService.php';
    require_once __DIR__ . '/../src/Support/EventThumbnailService.php';
    require_once __DIR__ . '/../src/Support/EventTableService.php';
    require_once __DIR__ . '/../src/Support/LoveStoryService.php';
    require_once __DIR__ . '/../src/Support/InviteStatsService.php';
    require_once __DIR__ . '/../src/Support/InviteStatusService.php';
    require_once __DIR__ . '/../src/Support/ConfirmationService.php';
    require_once __DIR__ . '/../src/Support/EventBackofficeService.php';
    require_once __DIR__ . '/../src/Support/EventWorkspaceService.php';
    require_once __DIR__ . '/../src/Support/AdminDashboardStatsService.php';
    require_once __DIR__ . '/../src/Support/RsvpService.php';
    require_once __DIR__ . '/../src/Support/PageRouter.php';
}