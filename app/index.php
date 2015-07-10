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
        "driver" => "pdo_mysql",
        "host" => "localhost",
        "dbname" => "dupe",
        "user" => "root",
        "password" => "123"
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
            unset($query[0]);

            $sql = "SELECT * FROM GAME_db WHERE releasename ";
            $input = [];
            foreach ($query as $q) {
                $sql .= "LIKE ? AND releasename ";
                $input[] = "%{$q}%";
            }

            $sql = substr($sql, 0, strlen($sql)-17);

            $data = $app["db"]->fetchAll($sql, $input);

            if (count($data) == 0) {
                throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("release not found");
            }

            return new \Symfony\Component\HttpFoundation\JsonResponse([$data]);
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
