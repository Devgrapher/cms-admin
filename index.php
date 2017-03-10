<?php
use Ridibooks\Platform\Cms\Admin\Controller\MenuControllerProvider as AdminMenuController;
use Ridibooks\Platform\Cms\Admin\Controller\TagControllerProvider as AdminTagController;
use Ridibooks\Platform\Cms\Admin\Controller\UserControllerProvider as AdminUserController;
use Ridibooks\Platform\Cms\Auth\LoginService;
use Ridibooks\Platform\Cms\CmsApplication;
use Ridibooks\Platform\Cms\MiniRouter;
use Symfony\Component\HttpFoundation\Request;

if (is_readable('/htdocs/platform/config.php')) {
	require_once '/htdocs/platform/config.php';
} else {
	require_once 'config.local.php';
}

$autoloader = require __DIR__ . "/server/vendor/autoload.php";

if (isset(\Config::$COUCHBASE_ENABLE) && \Config::$COUCHBASE_ENABLE) {
	LoginService::startCouchbaseSession(\Config::$COUCHBASE_SERVER_HOSTS);
} else {
	LoginService::startSession();
}

$app = new CmsApplication([
    'cms' => [
        'url' => \Config::$CMS_SERVER_HOST,
        'login_path' => '/login',
        'thrift_path' => '/',
    ]
]);
$app['twig.path'] = [
	__DIR__ . '/server/views'
];

// Try MiniRouter first
$app->before(function (Request $request) {
	return MiniRouter::shouldRedirectForLogin($request, \Config::$ENABLE_SSL);
});

$app->mount('/super', new AdminUserController());
$app->mount('/super', new AdminTagController());
$app->mount('/super', new AdminMenuController());

$app->run();
