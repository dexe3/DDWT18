<?php
/**
 * Controller
 * User: reinardvandalen
 * Date: 05-11-18
 * Time: 15:25
 */

/* Require composer autoloader */
require __DIR__ . '/vendor/autoload.php';

/* Include model.php */
include 'model.php';

/* Connect to DB */
$db = connect_db('localhost', 'ddwt18_week3', 'ddwt18', 'ddwt18');

// Set credentials for authentication
$cred = set_cred('ddwt18', 'ddwt18');

/* Create Router instance */
$router = new \Bramus\Router\Router();

// Add routes here
$router->get('/', function() {
    echo 'Homepage';
});

/*
 * Before any api routes, check credentials
 * @params array $cred with username and password
 * @return String feedback, http_response_code for failed authentication (401)
 */

$router->before('GET|POST|PUT|DELETE', '/api/.*', function() use($cred){
    if (!check_cred($cred)){
        echo 'Authentication required.';
        http_response_code(401);
        die();
    }
    echo "Succesfully authenticated";
});

//Creating a mount for the api folder
$router->mount('/api', function() use ($router, $db, $cred){
    //Setting the headertype of /api/* to json
    header('Content-Type: application/json');
    //GET for homepage
    $router->get('/', function(){
    });

    /* GET for reading all series */
    $router->get('/series', function() use($db, $cred) {

        $series = get_series($db);
        echo json_encode($series);
    });
    /* GET for reading specific series */
    $router->get('/series/(\d+)', function($id) use($db){
        $serie = get_serieinfo($db, $id);
        echo json_encode($serie);
    });
    /* DELETE route for specific serie
    * @oarams int $id, object $db with database connection
    * @return json $feedback
    */
    $router->delete('/series/(\d+)', function($id) use ($db) {
        $feedback = remove_serie($db, $id);
        echo json_encode($feedback);

    });
    /* POST route for adding serie
     * @oarams int $id, object $db with database connection
     * @return json $feedback
     */
    $router->POST('/series', function() use($db) {
        $feedback = add_serie($db, $_POST);
        echo json_encode($feedback);
    });
    /* PUT route for updating serie
     * @oarams int $id, object $db with database connection
     * @return json $feedback
     */
    $router->put('/series/(\d+)', function($id) use ($db) {
        /*
         * Create array $_PUT and inserted input
         * @params int $id, object $db with database connection
         * return json $feedback
         */
        $_PUT = array();
        parse_str(file_get_contents('php://input'), $_PUT);
        $serie_info = $_PUT + ["serie_id" => $id];
        $feedback = update_serie($db, $serie_info);
        echo json_encode($feedback);
    });
});

//Setting 404 Error page
$router->set404(function(){
    header('HTTP/1.1 404 Not Found');
    echo '<h1>'.'This page does not exist'.'</h1>';
});

/* Run the router */
$router->run();
