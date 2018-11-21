<?php
/**
 * Model
 * User: reinardvandalen
 * Date: 05-11-18
 * Time: 15:25
 */

/* Enable error reporting */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/**
 * Check if the route exist
 * @param string $route_uri URI to be matched
 * @param string $request_type request method
 * @return bool
 *
 */
function new_route($route_uri, $request_type){
    $route_uri_expl = array_filter(explode('/', $route_uri));
    $current_path_expl = array_filter(explode('/',parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)));
    if ($route_uri_expl == $current_path_expl && $_SERVER['REQUEST_METHOD'] == strtoupper($request_type)) {
        return True;
    }
}

/*
 * Return the series count
 * @params object $pdo for db connection
 * @return int
 */
function getSeriesCount($pdo){
    $stmt = $pdo->prepare('SELECT count(id) as count from series');
    $stmt->execute();
    $seriesCnt = $stmt->fetch();
    $seriesCntVal = $seriesCnt['count'];
    return $seriesCntVal;
};

/*
 * Return a multidimensional array of series
 * @params array $pdo for db connection
 * @return array
 */

function getSeries($pdo){
    $stmt = $pdo->prepare('SELECT * from series');
    $stmt->execute();
    $series = $stmt->fetchAll();
    $series_exp = array();

    foreach($series as $key => $value){
        foreach($value as $user_key => $user_input){
            $series_exp[$key][$user_key] = htmlspecialchars($user_input);
        }
    }
    return $series_exp;
}

/*
 * Return a populated table with series
 * @params array $series with serie data
 * @return table
 */

function get_series_table($series){
    $table_exp = '<table class="table table-hover"><tbody>
<thead>
<tr><th>Title</th><th>Seasons</th><th>Serie info</th></tr>
</thead>

';
    foreach ($series as $key => $value){
        $table_exp .= '
    <tr>
        <td>'.$value['name'].'</td>
        <td>'.$value['seasons'].'</td>
        <td><a href="/DDWT18/week1/serie/?serie_id='.$value['id'].'" role="button" class="btn btn-primary">More info</a></td>
    </tr>
';
    }
    $table_exp .= '</table></tbody>';
    return $table_exp;
}

/*
 * Return data on a specific serie
 * @params array $pdo for db connection int $serie_id to identify a serie
 * @return array
 */

function get_series_info($pdo, $serie_id){
    $serie_info['serie_id'] = $serie_id;
    $stmt = $pdo->prepare('SELECT * from series WHERE id = ?');
    $stmt->execute(array($serie_info['serie_id']));
    $series = $stmt->fetch();
    $serie_info_exp = array();

    /* Create array with htmlspecialchars */
    foreach ($series as $key => $value){
        $serie_info_exp[$key] = htmlspecialchars($value);
    }
    $series = $serie_info_exp;
    return $series;
}

/*
 * Add series to the database
 * @params object $pdo for db connection
 * @global object $_POST with form input
 * @return array with feedback
 */

function add_series($pdo){
    if (
        /*
     * Check if form is empty
     * @global object $_POST
     * @return feedback
     */
        empty($_POST['Name']) or
        empty($_POST['Creator']) or
        empty($_POST['Seasons']) or
        empty($_POST['Abstract'])
    ) {
        return [
            'type' => 'danger',
            'message' => 'There was an error. Not all fields were filled in.'
        ];
    } else {
        /*
         * Check if seasons is numeric
         * @global object $_POST
         * @return feedback
         */
        if (!is_numeric($_POST['Seasons'])) {
            return [
                'type' => 'danger',
                'message' => 'There was an error. You should enter a number in the field Seasons.'
            ];
        } else {
            /*
             * Check if serie exists
             * @params $pdo for db connection
             * @global object $_POST with series information
             * @return feedback
             */
            $stmt = $pdo->prepare('SELECT * FROM series WHERE name = ?');
            $stmt->execute([$_POST['Name']]);
            $serie = $stmt->rowCount();
            if ($serie){
                return [
                    'type' => 'danger',
                    'message' => 'This series already exists.'
                ];
            } else {
                /*
                 * Create new series entry and assign id
                 * @params $pdo for db connection
                 * @global object $_POST with serie information
                 * @return feedback for success or failure
                 */
                $stmt = $pdo->prepare('SELECT MAX(id) as id FROM series');
                $stmt->execute();
                $id = $stmt->fetch();
                $id['id']++;
                //print_r($id); exit;
                $stmt = $pdo->prepare("INSERT INTO series (id, name, creator, seasons, abstract) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([
                    $id['id'],
                    $_POST['Name'],
                    $_POST['Creator'],
                    $_POST['Seasons'],
                    $_POST['Abstract']
                ]);
                $inserted = $stmt->rowCount();
                if ($inserted == 1) {
                    return [
                        'type' => 'success',
                        'message' => sprintf("Series '%s' added to Series Overview.", $_POST['Name'])
                    ];
                }
                else {
                    return [
                        'type' => 'danger',
                        'message' => 'There was an error. The series was not added. Try it again.'
                    ];
                }
            }
        }
    }
}

/*
 * Update existing serie
 * Check if form is empty
 * Check if seasons is numeric
 * Disallow a namechange for the serie
 * Insert updated information in the database
 * @params object $pdo for db connection
 * @global object $_POST with serie information
 * @return array feedback
 */

function update_series($pdo) {
    if (
        empty($_POST['Name']) or
        empty($_POST['Creator']) or
        empty($_POST['Seasons']) or
        empty($_POST['Abstract']) or
        empty($_POST['serie_id'])
    ){
        return [
            'type' => 'danger',
            'message' => 'There was an error. Not all fields were filled in.'
        ];
    } else {
        if (!is_numeric($_POST['Seasons'])) {
            return [
                'type' => 'danger',
                'message' => 'There was an error. You should enter a number in the field Seasons.'
            ];
        } else {
            $stmt = $pdo->prepare('SELECT * FROM series WHERE id = ?');
            $stmt->execute(array($_POST['serie_id']));
            $serie = $stmt->fetch();
            $current_name = $serie['name'];

            $stmt = $pdo->prepare('SELECT * FROM series WHERE name =?');
            $stmt->execute(array($_POST['Name']));
            $serie = $stmt->fetch();
            if($_POST['Name'] == $serie['name'] and $serie['name'] != $current_name){
                return [
                    'type' => 'danger',
                    'message' => sprintf("The name of the series cannot be changed. %s already exists.", $_POST['Name'])
                ];
            } elseif ($_POST['Name'] == $serie['name'] and $serie['name'] == $current_name){
                $stmt = $pdo->prepare('UPDATE series SET name = ?, creator = ?, seasons = ?, abstract = ? WHERE id = ?');
                $stmt->execute([
                    $_POST['Name'],
                    $_POST['Creator'],
                    $_POST['Seasons'],
                    $_POST['Abstract'],
                    $_POST['serie_id']
                ]);
                $updated = $stmt->rowCount();
                if ($updated == 1) {
                    return [
                        'type' => 'success',
                        'message' => sprintf("Series '%s' was edited!", $_POST['Name'])
                    ];
                }
                else {
                    return [
                        'type' => 'warning',
                        'message' => 'The series was not edited. No changes were detected'
                    ];
                }

            }
        }
    }
}

/*
 * Remove serie from database
 * Check if the form id corresponds with serie_id from page
 * @params object $pdo for db connection, int $serie_id from page
 * @global object $_POST with serie information
 * @return array feedback
 */

function remove_serie($pdo, $serie_id){
    $serie_info = get_series_info($pdo, $serie_id);
    if($_POST['serie_id'] == $serie_info['id']){
        $stmt = $pdo->prepare('DELETE FROM series WHERE id = ?');
        $stmt->execute([$_POST['serie_id']]);
        $deleted = $stmt->rowCount();
        if ($deleted == 1) {
            return [
                'type' => 'success',
                'message' => sprintf("Series '%s' was removed!", $serie_info['name'])
            ];
        }
        else {
            return [
                'type' => 'warning',
                'message' => 'An error occurred. The series was not removed.'
            ];
        }
    }
}

/**
 * Creates a new navigation array item using url and active status
 * @param string $url The url of the navigation item
 * @param bool $active Set the navigation item to active or inactive
 * @return array
 */
function na($url, $active){
    return [$url, $active];
}

/**
 * Creates filename to the template
 * @param string $template filename of the template without extension
 * @return string
 */
function use_template($template){
    $template_doc = sprintf("views/%s.php", $template);
    return $template_doc;
}

/**
 * Creates breadcrumb HTML code using given array
 * @param array $breadcrumbs Array with as Key the page name and as Value the corresponding url
 * @return string html code that represents the breadcrumbs
 */
function get_breadcrumbs($breadcrumbs) {
    $breadcrumbs_exp = '<nav aria-label="breadcrumb">';
    $breadcrumbs_exp .= '<ol class="breadcrumb">';
    foreach ($breadcrumbs as $name => $info) {
        if ($info[1]){
            $breadcrumbs_exp .= '<li class="breadcrumb-item active" aria-current="page">'.$name.'</li>';
        }else{
            $breadcrumbs_exp .= '<li class="breadcrumb-item"><a href="'.$info[0].'">'.$name.'</a></li>';
        }
    }
    $breadcrumbs_exp .= '</ol>';
    $breadcrumbs_exp .= '</nav>';
    return $breadcrumbs_exp;
}

/**
 * Creates navigation HTML code using given array
 * @param array $navigation Array with as Key the page name and as Value the corresponding url
 * @return string html code that represents the navigation
 */
function get_navigation($navigation){
    $navigation_exp = '<nav class="navbar navbar-expand-lg navbar-light bg-light">';
    $navigation_exp .= '<a class="navbar-brand">Series Overview</a>';
    $navigation_exp .= '<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">';
    $navigation_exp .= '<span class="navbar-toggler-icon"></span>';
    $navigation_exp .= '</button>';
    $navigation_exp .= '<div class="collapse navbar-collapse" id="navbarSupportedContent">';
    $navigation_exp .= '<ul class="navbar-nav mr-auto">';
    foreach ($navigation as $name => $info) {
        if ($info[1]){
            $navigation_exp .= '<li class="nav-item active">';
            $navigation_exp .= '<a class="nav-link" href="'.$info[0].'">'.$name.'</a>';
        }else{
            $navigation_exp .= '<li class="nav-item">';
            $navigation_exp .= '<a class="nav-link" href="'.$info[0].'">'.$name.'</a>';
        }

        $navigation_exp .= '</li>';
    }
    $navigation_exp .= '</ul>';
    $navigation_exp .= '</div>';
    $navigation_exp .= '</nav>';
    return $navigation_exp;
}

/**
 * Pritty Print Array
 * @param $input
 */
function p_print($input){
    echo '<pre>';
    print_r($input);
    echo '</pre>';
}

/**
 * Creats HTML alert code with information about the success or failure
 * @param bool $type True if success, False if failure
 * @param string $message Error/Success message
 * @return string
 */
function get_error($feedback){
    $error_exp = '
        <div class="alert alert-'.$feedback['type'].'" role="alert">
            '.$feedback['message'].'
        </div>';
    return $error_exp;
}

/**
 * Set up connection with the database, whith attributes defined in index.php.
 * The try catch method retuns an error message when the connection fails
 */

/*
 * connection to database
 * @params input for databse connection
 * @return object $pdo if success or error message if failed
 */

function connect ($username, $password, $db, $host, $charset){
    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];
    try {
        $pdo = new PDO($dsn, $username, $password, $options);
        return $pdo;
    } catch (\PDOException $e) {
        echo sprintf("Failed to connect. %s", $e->getMessage());
    }
};


?>
