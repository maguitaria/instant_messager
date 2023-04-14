/**NOTE -
This file is responsible for connecting to server, logging and cookies settings
*
*/
<?php
// Initialize sessions
// Database connection credentials
// $database_host= getenv('DB_HOST');
// $database_name= getenv('DB_NAME');
// $database_user= getenv('DB_USER');
// $database_password=getenv('DB_PASSWORD');
// echo($database_host);

session_start();
$database_host = 'localhost';
$database_name = 'phpsupportchat';
$database_user = 'root';
$database_password = '1804';
try {
    // Attempt to connect to db
    $pdo = new PDO('mysql:host=localhost;dbname=' . $database_name . ';charset=utf8', $database_user, $database_password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $exception) {
    exit('Failed to connect to db!');
}
/**!SECTION
 * @function whether the user is logged in
 */
function is_loggedin($pdo)
{
    if (isset($_SESSION['account_loggedin'])) {
        // Update the last seed date
        $stmt = $pdo->prepare('update accounts SET last_seen = ? WHERE id = ?');
        $stmt->execute([date('Y-m-d H:i:s'), $_SESSION['account_id']]);
        return TRUE;
    }

    // check if there is a secret cookie declared in the brwoser cookies
    if (isset($_COOKIE['chat-secret']) && !empty($_COOKIE['chat-secret'])) {
        $stmt = $pdo->prepare('SELECT * FROM accounts WHERE secret = ?');
        $stmt->execute([$_COOKIE['chat-secret']]);
        $account = $stmt->fetch(PDO::FETCH_ASSOC);
        //check if account exists
        if ($account) {
            // authenticate the user
            $_SESSION['account_loggedin'] = true;
            $_SESSION['account_id'] = $account['id'];
            $_SESSION['account_role'] = $account['role'];
            return true;
        }
    }
    //user is not logged in
    return false;
}
$secret_key = bin2hex(random_bytes(16));
echo $secret_key;
// update user`s secret code in db
function update_secret($pdo, $id, $email, $current_secret = [''])
{
    $cookiehash = !empty($current_secret) ? $current_secret : password_hash($id . $email . '646e8d961c8938052c059748202ffd89', PASSWORD_DEFAULT);
    $days = 30;
    // create new cookie
    setcookie('chat-secret', $cookiehash, (int) (time() + 60 * 60 * 24 * $days));
    // update the secret code in db
    $stmt = $pdo->prepare('UPDATE accounts SET secret = ? WHERE id = ?');
    $stmt->execute([$cookiehash, $id]);
}
// assign a unique color to our users
function color_from_string($string)
{
    $colors = ['#34568B', '#FF6F61', '#6B5B95', '#88B04B', '#F7CAC9', '#92A8D1', '#955251', '#B565A7', '#009B77', '#DD4124', '#D65076', '#45B8AC', '#EFC050', '#5B5EA6', '#9B2335', '#DFCFBE', '#BC243C', '#C3447A', '#363945', '#939597', '#E0B589', '#926AA6', '#0072B5', '#E9897E', '#B55A30', '#4B5335', '#798EA4', '#00758F', '#FA7A35', '#6B5876', '#B89B72', '#282D3C', '#C48A69', '#A2242F', '#006B54', '#6A2E2A', '#6C244C', '#755139', '#615550', '#5A3E36', '#264E36', '#577284', '#6B5B95', '#944743', '#00A591', '#6C4F3D', '#BD3D3A', '#7F4145', '#485167', '#5A7247', '#D2691E', '#F7786B', '#91A8D0', '#4C6A92', '#838487', '#AD5D5D', '#006E51', '#9E4624'];
    $colorIndex = hexdec(substr(sha1($string), 0, 10)) % count($colors);
    return $colors[$colorIndex];
}