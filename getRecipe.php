<?php
session_start();

$servername = "127.0.0.1";
$username = "root";
$password = "secretsecret4";
$dbname = "schema_user";
$port = 3306;

$conn = new mysqli($servername, $username, $password, $dbname, $port);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "
    SELECT 
        r.recipeId, 
        r.name, 
        r.`desc`, 
        r.ingredients, 
        r.`procedure`, 
        r.`category`, 
        r.`img`,
        COALESCE(l.likeCount, 0) AS likeCount
    FROM 
        tbl_recipe r
    LEFT JOIN 
        (SELECT recipeId, COUNT(*) AS likeCount FROM tbl_likes GROUP BY recipeId) l 
    ON 
        r.recipeId = l.recipeId
";

$result = $conn->query($sql);
$recipes = array();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $recipes[] = $row;
    }
}

header('Content-Type: application/json');
echo json_encode($recipes);

$conn->close();
?>
