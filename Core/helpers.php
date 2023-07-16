<?php


use BoardRoom\Core\Mantle\App;
use BoardRoom\Core\Mantle\Auth;
use BoardRoom\Core\Mantle\Logger;
use BoardRoom\Core\Mantle\Request;
use BoardRoom\Core\Mantle\Session;
use BoardRoom\Core\Calendar;

define("BASE_URL",  sprintf(
    "%s://%s",
    isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
    $_SERVER['SERVER_NAME']
));

/**
 * checkCreateView
 * 
 * Create a view for route if it does not exist
 * 
 * @param string $view view to be created
 * 
 * @return void
 */
function checkView(string $filename) {
    if (!file_exists($filename)) {

        if (ENV === 'production') {
            throw new \Exception("The requested view is missing", 404);
        }
        fopen("$filename", 'a');

        $data = "<?php include_once 'base.view.php';?><div class=\"grid place-items-center h-screen\">
       Created {$filename}'s view; please edit</div>";

        file_put_contents($filename, $data);
    }
}

/**
 * View
 * 
 * Loads a specified file along with its data
 * 
 * @param string $filename Page to displayed
 * @param array $data Data to be passed along
 * 
 * @return bool view
 */
function view(string $filename, array $data = []) {
    extract($data);
    $filename = "Views/{$filename}.view.php";

    checkView($filename);

    return require_once $filename;
}

/**
 * Redirect
 * 
 * Redirects to a give page
 * 
 * @param string $path Page to be redirected to
 */
function redirect(string $path) {
    header("location:{$path}");
}
/**
 * Abort
 * 
 * Kills the execution of the script & diplays error page
 * 
 * @param string $message The exception/error msg
 * @param int $code Status code passed with the exception
 * 
 * @return string view
 */
function abort($message, $code) {

    if ($code === 0) {
        $code = 500;
        http_response_code(500);
    } elseif (is_string($code)) {
        http_response_code(500);
    } elseif ($code === "") {
        $code =  substr($message, -5, strpos($message, '!'));
        http_response_code(500);
    } else {
        http_response_code($code);
    }
    //logger("Error", "Exception: $message");
    view('error', [
        'code' => $code,
        'message' => $message
    ]);
    exit;
}

function redirectback($data = []) {
    extract($data);
    if (isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] !== '') {
        redirect($_SERVER['HTTP_REFERER']);
    }
    $back = (new Request)->get('back');
    if (!$back) {
        redirect('/');
    }
    redirect($back);
}

function request_uri() {
    return Request::uri();
}


function wp_strip_all_tags($string, $remove_breaks = false) {
    $string = preg_replace('@<(script|style)[^>]*?>.*?</\\1>@si', '', $string);
    $string = strip_tags($string);

    if ($remove_breaks) {
        $string = preg_replace('/[\r\n\t ]+/', ' ', $string);
    }

    return trim($string);
}



function getRandColor() {
    $rgbColor = [];
    foreach (['r', 'g', 'b'] as $color) {
        //Generate a random number between 0 and 255.
        $rgbColor[$color] = mt_rand(0, 255);
    }
    $colorCode = implode(",", $rgbColor);
    return "rgb($colorCode)";
}
/**
 * subtract_date
 * 
 * Subtracts a number of days from a date
 * 
 * @param  string $days_to_subtract no of days to subtract
 * 
 * @return string the date after subtracting
 */

function subtract_date($days_to_subtract) {
    $date = date_create(date('Y-m-d H:i:s', time()));
    date_sub($date, date_interval_create_from_date_string("2 days"));
    return date_format($date, 'Y-m-d H:i:s');
}

/**
 * Converts a time string to 10 AM.
 *
 * @param string $time The time string in 24-hour format.
 * @return string The time in 10 AM format.
 */
function get_time(string $time): string {
    $hour = date("G", strtotime($time));

    if ($hour >= 12) {
        $am_pm = "PM";
        if ($hour > 12) {
            $hour -= 12;
        }
    } else {
        $am_pm = "AM";
        if ($hour == 0) {
            $hour = 12;
        }
    }

    return "$hour$am_pm";
}



function slug($string) {
    return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $string)));
}


function isAdmin() {
    if (auth() && auth()->role === 'admin') {
        return true;
    }
    return false;
}
/**
 * Auth Helper
 * 
 * Returns the status of login & an object helper
 * 
 * @return bool|object Session
 */
function auth() {

    if (Session::get('loggedIn') === NULL || Session::get('loggedIn') === false) {
        return false;
    }

    Session::get('loggedIn');
    $class = new class {

        public $username;
        public $email;
        public $role;
        public $id;

        public function __construct() {

            $this->username = Session::get('user') ?? null;
            $this->email = Session::get('email') ?? null;
            $this->id = Session::get('user_id') ?? null;
            $this->role = Session::get('role') ?? null;
        }
        public function __get($name) {
            return $name;
        }
        public function __set($name, $value) {
            $this->$name = $value;
        }
        public function logout($user) {
            Auth::logout($user);
            redirect('/');
        }
    };

    return $class;
}
/**
 * plural
 * This returns the plural version of common english words
 * --from stackoverflow
 * 
 * @param string $phrase the word to be pluralised
 * @param int $value 
 * 
 * @return string plural 
 */
function plural($phrase, $value) {
    $plural = '';
    if ($value > 1) {
        for ($i = 0; $i < strlen($phrase); $i++) {
            if ($i == strlen($phrase) - 1) {
                $plural .= ($phrase[$i] == 'y') ? 'ies' : (($phrase[$i] == 's' || $phrase[$i] == 'x' || $phrase[$i] == 'z' || $phrase[$i] == 'ch' || $phrase[$i] == 'sh') ? $phrase[$i] . 'es' : $phrase[$i] . 's');
            } else {
                $plural .= $phrase[$i];
            }
        }
        return $plural;
    }
    return $phrase;
}

/**
 * Delete a file
 */
function delete_file(string $path) {
    if (!unlink($path)) {
        logger("Error", "Delete File: File cannot be deleted due to an error");
        return false;
    } else {
        logger("Info", "Delete File: A File has been deleted");
        return true;
    }
}


function downloadFile($dir, $file) {

    if (file_exists($file . "uuj")) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($file) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));
        flush(); // Flush system output buffer
        readfile($dir . $file);
        die();
    } else {
        http_response_code(404);
        die();
    }
}


/**
 * dd
 * 
 * dump the results & die
 * 
 * @param mixed $data view to be created
 * 
 * @return string
 */

function dd($var) {
    //to do
    // debug_print_backtrace();

    ini_set("highlight.keyword", "#a50000;  font-weight: bolder");
    ini_set("highlight.string", "#5825b6; font-weight: lighter; ");

    ob_start();
    highlight_string("<?php\n" . var_export($var, true) . "?>");
    $highlighted_output = ob_get_clean();

    $highlighted_output = str_replace(["&lt;?php", "?&gt;"], '', $highlighted_output);

    echo $highlighted_output;
    die();
}
/**
 * url helper
 * 
 * @return string url in relation to where it is called
 * 
 * from https://stackoverflow.com/questions/2820723/how-do-i-get-the-base-url-with-php
 */
function url() {
    if (!is_dev()) {
        return sprintf(
            "%s://%s%s",
            isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
            $_SERVER['SERVER_NAME'],
            $_SERVER['REQUEST_URI']
        );
    } else {
        return sprintf(
            "%s://%s:%s%s",
            isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
            $_SERVER['SERVER_NAME'],
            $_SERVER['SERVER_PORT'],
            $_SERVER['REQUEST_URI']
        );
    }
}

function notify($message) {
    echo '<script type="text/javascript">',
    "notify('$message');",
    '</script>';
}
function format_date($date) {
    return date("jS M Y ", strtotime($date));
}

function time_ago($datetime, $full = false) {
    $now = new \DateTime;
    $ago = new \DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}
/**
 * asset helper
 * 
 * @param $dir director to be returned in respect to the static dir
 * 
 * @return string Path to the requested resource
 */
function asset($dir) {
    $baseUrl = BASE_URL;
    $serverPort = $_SERVER['SERVER_PORT'] ?? '';

    if (!empty($serverPort)) {
        $baseUrl .= ':' . $serverPort;
    }

    echo $baseUrl . "/static/$dir";
}
function get_perc($total, $number) {
    if ($total > 0) {
        return round(($number * 100) / $total, 2);
    } else {
        return 0;
    }
}

function logger(string $level, string $message) {
    Logger::log($level, $message);
}

function request(string $key = "") {
    if ($key !== "") {
        return htmlspecialchars(trim($_REQUEST[$key])) ?? NULL;
    }

    return new class {
        public function all() {
            return array_merge($_POST, $_GET);
        }
    };
}


function get_notifications() {
    if (empty(Session::get("notifications"))) {
        return [];
    }
    return Session::get("notifications");
}
function delete_notifications() {
    return Session::unset("notifications");
}
function session_get($value) {
    return Session::get($value);
}

function get_errors() {
    if (empty(Request::$errors)) {
        return [];
    }
    return Request::$errors;
}
/**
 * Format duration from db
 * 
 * @example 
 * "00:30:00"  to be 30 mins
 * "00:45:00"  to be 45 mins
 * "01:30:00"   to be 1hr 30mins
 */
function format_time_to_minutes($time_string) {
    // Get the full time string.
    $time = strtotime($time_string);

    // Get the hours and minutes from the time string.
    $hours = (int)date("H", $time);
    $minutes = (int)date("i", $time);

    // If the hours are 0 and the minutes are less than 60, just return them.
    if ($hours === 0 && $minutes < 60) {
        return strval($minutes) . "mins";
    }
    if ($minutes === 0) {
        return strval($hours) . "hrs";
    }

    // Otherwise, return the time in the format "1hr 30mins".
    return sprintf("%dhrs %dmins", $hours, $minutes);
}

function format_meeting_date(string $dateString) {
    $timestamp = strtotime($dateString);
    return date("F jS, Y \a\\t g:i A", $timestamp);
}



function is_dev() {
    if (ENV === 'development') {
        return true;
    } elseif (ENV === 'production') {
        return false;
    }
}

/**
 * singularize
 * This returns the singular version of common english words
 * --from https://www.kavoir.com/2011/04/php-class-converting-plural-to-singular-or-vice-versa-in-english.html
 * 
 * @param string $phrase the word to be pluralised
 * @param int $value 
 * 
 * @return string plural 
 */

function singularize($word) {
    $singular = array(
        '/(quiz)zes$/i' => '\1',
        '/(matr)ices$/i' => '\1ix',
        '/(vert|ind)ices$/i' => '\1ex',
        '/^(ox)en/i' => '\1',
        '/(alias|status)es$/i' => '\1',
        '/([octop|vir])i$/i' => '\1us',
        '/(cris|ax|test)es$/i' => '\1is',
        '/(shoe)s$/i' => '\1',
        '/(o)es$/i' => '\1',
        '/(bus)es$/i' => '\1',
        '/([m|l])ice$/i' => '\1ouse',
        '/(x|ch|ss|sh)es$/i' => '\1',
        '/(m)ovies$/i' => '\1ovie',
        '/(s)eries$/i' => '\1eries',
        '/([^aeiouy]|qu)ies$/i' => '\1y',
        '/([lr])ves$/i' => '\1f',
        '/(tive)s$/i' => '\1',
        '/(hive)s$/i' => '\1',
        '/([^f])ves$/i' => '\1fe',
        '/(^analy)ses$/i' => '\1sis',
        '/((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/i' => '\1\2sis',
        '/([ti])a$/i' => '\1um',
        '/(n)ews$/i' => '\1ews',
        '/s$/i' => '',
    );

    $uncountable = array('equipment', 'information', 'rice', 'money', 'species', 'series', 'fish', 'sheep');

    $irregular = array(
        'person' => 'people',
        'man' => 'men',
        'child' => 'children',
        'sex' => 'sexes',
        'move' => 'moves'
    );

    $lowercased_word = strtolower($word);
    foreach ($uncountable as $_uncountable) {
        if (substr($lowercased_word, (-1 * strlen($_uncountable))) == $_uncountable) {
            return $word;
        }
    }

    foreach ($irregular as $_plural => $_singular) {
        if (preg_match('/(' . $_singular . ')$/i', $word, $arr)) {
            return preg_replace('/(' . $_singular . ')$/i', substr($arr[0], 0, 1) . substr($_plural, 1), $word);
        }
    }

    foreach ($singular as $rule => $replacement) {
        if (preg_match($rule, $word)) {
            return preg_replace($rule, $replacement, $word);
        }
    }

    return $word;
}

const DEFAULT_VALIDATION_ERRORS = [
    'required' => 'Please enter the %s',
    'email' => 'The %s is not a valid email address',
    'min' => 'The %s must have at least %s characters',
    'max' => 'The %s must have at most %s characters',
    'between' => 'The %s must have between %d and %d characters',
    'same' => 'The %s must match with %s',
    'alphanumeric' => 'The %s should have only letters and numbers',
    'secure' => 'The %s must have between 8 and 64 characters and contain at least one number, one upper case letter, one lower case letter and one special character',
    'unique' => 'The %s already exists',
];


/**
 * Return true if a string is not empty
 * @param array $data
 * @param string $field
 * @return bool
 */
function is_required(array $data, string $field): bool {
    return isset($data[$field]) && trim($data[$field]) !== '';
}

/**
 * Return true if the value is a valid email
 * @param array $data
 * @param string $field
 * @return bool
 */
function is_email(array $data, string $field): bool {
    if (empty($data[$field])) {
        return true;
    }

    return filter_var($data[$field], FILTER_VALIDATE_EMAIL);
}

/**
 * Return true if a string has at least min length
 * @param array $data
 * @param string $field
 * @param int $min
 * @return bool
 */
function is_min(array $data, string $field, int $min): bool {
    if (!isset($data[$field])) {
        return true;
    }

    return mb_strlen($data[$field]) >= $min;
}

/**
 * Return true if a string cannot exceed max length
 * @param array $data
 * @param string $field
 * @param int $max
 * @return bool
 */
function is_max(array $data, string $field, int $max): bool {
    if (!isset($data[$field])) {
        return true;
    }

    return mb_strlen($data[$field]) <= $max;
}

/**
 * @param array $data
 * @param string $field
 * @param int $min
 * @param int $max
 * @return bool
 */
function is_between(array $data, string $field, int $min, int $max): bool {
    if (!isset($data[$field])) {
        return true;
    }

    $len = mb_strlen($data[$field]);
    return $len >= $min && $len <= $max;
}

/**
 * Return true if a string equals the other
 * @param array $data
 * @param string $field
 * @param string $other
 * @return bool
 */
function is_same(array $data, string $field, string $other): bool {
    if (isset($data[$field], $data[$other])) {
        return $data[$field] === $data[$other];
    }

    if (!isset($data[$field]) && !isset($data[$other])) {
        return true;
    }

    return false;
}

/**
 * Return true if a string is alphanumeric
 * @param array $data
 * @param string $field
 * @return bool
 */
function is_alphanumeric(array $data, string $field): bool {
    if (!isset($data[$field])) {
        return true;
    }

    return ctype_alnum($data[$field]);
}

/**
 * Return true if a password is secure
 * @param array $data
 * @param string $field
 * @return bool
 */
function is_secure(array $data, string $field): bool {
    if (!isset($data[$field])) {
        return false;
    }

    $pattern = "#.*^(?=.{8,64})(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*\W).*$#";
    return preg_match($pattern, $data[$field]);
}


/**
 * Connect to the database and returns an instance of PDO class
 * or false if the connection fails
 *
 * @return \PDO
 */
function db(): \PDO {
    return App::get('database');
}

/**
 * Return true if the $value is unique in the column of a table
 * @param array $data
 * @param string $field
 * @param string $table
 * @param string $column
 * @return bool
 */
function is_unique(array $data, string $field, string $table, string $column): bool {
    if (!isset($data[$field])) {
        return true;
    }

    $sql = "SELECT $column FROM $table WHERE $column = :value";

    $stmt = db()->prepare($sql);
    $stmt->bindValue(":value", $data[$field]);

    $stmt->execute();

    return $stmt->fetchColumn() === false;
}
function build_table($array) {
    // start table
    $html = "<table class=\"w-full text-sm text-left text-gray-500 dark:text-gray-400\">";
    // header row
    $html .= "<thead class=\"sticky top-0  text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400\">";
    $html .= '<tr>';
    foreach ($array[0] as $key => $value) {
        $html .= '<th  scope="col" class="sticky top-0  px-6 py-3" >' . htmlspecialchars($key) . '</th>';
    }
    $html .= '</tr>';
    $html .= "</thead>";
    // data rows
    $html .= ' <tbody class="overflow-y-auto">';
    foreach ($array as $key => $value) {
        $html .= '<tr class="border-b dark:bg-gray-800 dark:border-gray-700 odd:bg-white even:bg-gray-50 odd:dark:bg-gray-800 even:dark:bg-gray-700">';
        foreach ($value as $key2 => $value2) {
            $html .= '<td class="px-6 py-4">' . htmlspecialchars($value2) . '</td>';
        }
        $html .= '</tr>';
    }
    $html .= ' </tbody>';
    // finish table and return it

    $html .= '</table>';

    return $html;
}
