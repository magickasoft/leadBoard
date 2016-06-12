<?php
use Phalcon\Loader;
use Phalcon\Mvc\Micro;
use Phalcon\Di\FactoryDefault;
use Phalcon\Db\Adapter\Pdo\Mysql as PdoMysql;
use Phalcon\Http\Response;
use Phalcon\Http\Request;


$loader = new Loader();

$loader->registerDirs(
    array(
        __DIR__ . '/models/'
    )
)->register();

$di = new FactoryDefault();


$di->set('db', function () {
    return new PdoMysql(
        array(
			'host'        => 'localhost',
			'username'    => 'root',
			'password'    => '',
			'dbname'      => 'test1',
			'charset'     => 'utf8'
        )
    );
});

// Создаем и привязываем DI к приложению
$app = new Micro($di);

// Получение всех лидов
$app->get('/api/leadBoard', function () use ($app) {
	header('Content-type:application/json;charset=utf-8');
    $phql = "SELECT * FROM LeadBoards ORDER BY name LIMIT 50";
    $leads = $app->modelsManager->executeQuery($phql);
	
    $data = array();
    foreach ($leads as $lead) {
        $data[] = array(
            'id'   => $lead->id,
			'name' => $lead->name,
			'place' => $lead->place,
			'score' => $lead->score,
			'avatar' => $lead->avatar,
        );
		
    }
    echo json_encode(array(
                'status' => 'OK',
                'leaderboard'   => $data
            ));

});
$app->get('/', function () use ($app) {
	header('Content-type:application/json;charset=utf-8');
    $phql = "SELECT * FROM LeadBoards ORDER BY name LIMIT 50";
    $leads = $app->modelsManager->executeQuery($phql);
	
    $data = array();
    foreach ($leads as $lead) {
        $data[] = array(
            'id'   => $lead->id,
			'name' => $lead->name,
			'place' => $lead->place,
			'score' => $lead->score,
			'avatar' => $lead->avatar,
        );
		
    }
    echo json_encode(array(
                'status' => 'OK',
                'leaderboard'   => $data
            ));
	
});

// Поиск лидов с $name в названии
$app->get('/api/leadBoard/search/{name}', function ($name) use ($app) {
	header('Content-type:application/json;charset=utf-8');
	$phql = "SELECT * FROM LeadBoards WHERE name LIKE :name: ORDER BY name";
    $leads = $app->modelsManager->executeQuery(
        $phql,
        array(
            'name' => '%' . $name . '%'
        )
    );

    $data = array();
    foreach ($leads as $lead) {
        $data[] = array(
            'id'   => $lead->id,
            'name' => $lead->name,
			'place' => $lead->place,
			'score' => $lead->score,
			'avatar' => $lead->avatar
        );
    }
	echo json_encode($data);

});

// Получение лида по первичному ключу
$app->get('/api/leadBoard/{id:[0-9]+}', function ($id) use ($app) {
	$phql = "SELECT * FROM LeadBoards WHERE id = :id:";
    $lead = $app->modelsManager->executeQuery($phql, array(
        'id' => $id
    ))->getFirst();


    $response = new Response();
	$response->setContentType("application/json");
    if ($lead == false) {
        $response->setJsonContent(
            array(
                'status' => 'NOT-FOUND'
            )
        );
    } else {
        $response->setJsonContent(
            array(
                'status' => 'FOUND',
                'data'   => array(
					'id'   => $lead->id,
					'name' => $lead->name,
					'place' => $lead->place,
					'score' => $lead->score,
					'avatar' => $lead->avatar
				)
            )
        );
    }
    return $response;

});

// Добавление нового лида
$app->post('/api/leadBoard', function () use ($app) {
	
   $lead = $app->request->getJsonRawBody();
   $phql = "INSERT INTO LeadBoards (name, place, score, avatar) VALUES (:name:, :place:, :score:, :avatar:)";

    $status = $app->modelsManager->executeQuery($phql, array(
        'name' => $lead->name,
        'place' => $lead->place,
        'score' => $lead->score,
		'avatar' => $lead->avatar
    ));


    $response = new Response();
	$response->setContentType("application/json");

    if ($status->success() == true) {


        $response->setStatusCode(201, "Created");

        $lead->id = $status->getModel()->id;

        $response->setJsonContent(
            array(
                'status' => 'OK',
                'data'   => $lead
            )
        );

    } else {


        $response->setStatusCode(409, "Conflict");


        $errors = array();
        foreach ($status->getMessages() as $message) {
            $errors[] = $message->getMessage();
        }

        $response->setJsonContent(
            array(
                'status'   => 'ERROR',
                'messages' => $errors
            )
        );
    }
    return $response;


});

// Обновление лида по первичному ключу
$app->put('/api/leadBoard/{id:[0-9]+}', function ($id) use ($app) {
	$lead = $app->request->getJsonRawBody();

    $phql = "UPDATE LeadBoards SET name = :name:, place = :place:, score = :score:, avatar = :avatar: WHERE id = :id:";
    $status = $app->modelsManager->executeQuery($phql, array(
        'id' => $id,
        'name' => $lead->name,
        'place' => $lead->place,
        'score' => $lead->score,
		'avatar' => $lead->avatar
    ));


    $response = new Response();
	$response->setContentType("application/json");
    if ($status->success() == true) {
        $response->setJsonContent(
            array(
                'status' => 'OK'
            )
        );
    } else {

        $response->setStatusCode(409, "Conflict");

        $errors = array();
        foreach ($status->getMessages() as $message) {
            $errors[] = $message->getMessage();
        }

        $response->setJsonContent(
            array(
                'status'   => 'ERROR',
                'messages' => $errors
            )
        );
    }
    return $response;

});

// Удаление лида по первичному ключу
$app->delete('/api/leadBoard/{id:[0-9]+}', function ($id) use ($app) {
	$phql = "DELETE FROM LeadBoards WHERE id = :id:";
    $status = $app->modelsManager->executeQuery($phql, array(
        'id' => $id
    ));

    $response = new Response();
	$response->setContentType("application/json");
    if ($status->success() == true) {
        $response->setJsonContent(
            array(
                'status' => 'OK'
            )
        );
    } else {

        $response->setStatusCode(409, "Conflict");

        $errors = array();
        foreach ($status->getMessages() as $message) {
            $errors[] = $message->getMessage();
        }

        $response->setJsonContent(
            array(
                'status'   => 'ERROR',
                'messages' => $errors
            )
        );
    }
    return $response;

});

$app->handle();

?>