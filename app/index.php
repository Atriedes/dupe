<?php


if (preg_match('/\.(?:png|jpg|jpeg|gif|html|css|js)$/', $_SERVER["REQUEST_URI"])) {
    return false;
}

require __DIR__ . "/../vendor/autoload.php";

$app = new \Silex\Application([
    "debug" => false,
]);

$app->register(new \Silex\Provider\DoctrineServiceProvider(), [
    "db.options" => [
        "read" => [
            "driver" => "pdo_mysql",
            "host" => "localhost",
            "dbname" => "dupe",
            "user" => "root",
            "password" => "123"
        ]
    ]
]);

$app->before(function(\Symfony\Component\HttpFoundation\Request $request, \Silex\Application $app) {
    if ($request->getClientIp() != "127.0.0.1") {
        throw new \Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException("invalid ip");
    }
});

$app->post("/request", function(\Symfony\Component\HttpFoundation\Request $request) use ($app) {
    $query = $request->get("query");

    $query = explode(" ", $query);
    $command = $query[0];

    switch ($command) {
        case "!dupe":
            if (count($query) == 0) {
                throw new \Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException("parameter required");
            }
            return new \Symfony\Component\HttpFoundation\JsonResponse([[
                "date" => "2014-10-21 08:22:12",
                "section" => "[0DAY]",
                "title" => "Some.Software.v1.0.Incl.Keymaker-CORE",
                "size" => "2MB",
                "disk" => "1"
            ]]);
            break;
        case "!nuke":
            break;
        case "!stats":
            break;
        default:
            throw new \Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException("command is not found");
    }
});

$app->error(function(\Exception $e) {
    return new \Symfony\Component\HttpFoundation\JsonResponse(["error" => true, "message" => $e->getMessage()
    ]);
});

$app->run();
