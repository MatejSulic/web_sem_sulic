<?php
$servername = "localhost";
$username = "root"; // v XAMPPu je obvykle root bez hesla
$password = "";
$dbname = "testdb";

// Připojení k MySQL
$conn = new mysqli($servername, $username, $password);
if ($conn->connect_error) {
    die("<div class='alert alert-danger'>Spojení s MySQL selhalo: " . $conn->connect_error . "</div>");
}

// Vytvoření databáze a tabulky, pokud neexistují
$conn->query("CREATE DATABASE IF NOT EXISTS $dbname");
$conn->select_db($dbname);
$conn->query("CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL
)");

// Pokud AJAX posílá nový uživatele
if(isset($_POST['new_user'])){
    $name = $conn->real_escape_string($_POST['new_user']);
    if(!empty($name)){
        $conn->query("INSERT INTO users (name) VALUES ('$name')");
        echo "OK";
    } else {
        echo "ERR";
    }
    exit;
}
?>

<!DOCTYPE html>

<html lang="cs">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Overkill Test PHP + MySQL + Bootstrap + AJAX</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">

<div class="container">
    <h1 class="text-primary">Overkill Testovací stránka</h1>

<!-- PHP test -->
<div class="alert alert-success mt-3">
    PHP funguje! Datum a čas serveru: <?php echo date("Y-m-d H:i:s"); ?>
</div>

<!-- MySQL test -->
<div class="alert alert-info">Spojení s MySQL je OK!</div>

<!-- Formulář pro přidání uživatele -->
<div class="mb-3 mt-4">
    <label for="username" class="form-label">Přidat uživatele:</label>
    <div class="input-group">
        <input type="text" id="username" class="form-control" placeholder="Jméno uživatele">
        <button class="btn btn-primary" id="addUserBtn">Přidat</button>
    </div>
    <div id="userAlert" class="mt-2"></div>
</div>

<!-- Seznam uživatelů -->
<h3 class="mt-4">Uživatelé v databázi:</h3>
<ul class="list-group" id="userList">
    <?php
    $sql = "SELECT id, name FROM users";
    $result = $conn->query($sql);
    if($result->num_rows > 0){
        while($row = $result->fetch_assoc()){
            echo "<li class='list-group-item'>" . $row['id'] . ": " . $row['name'] . "</li>";
        }
    }
    ?>
</ul>


</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.getElementById('addUserBtn').addEventListener('click', function(e){
    e.preventDefault();
    let name = document.getElementById('username').value.trim();
    let alertDiv = document.getElementById('userAlert');

    if(name === ""){
        alertDiv.innerHTML = '<div class="alert alert-danger">Jméno nemůže být prázdné!</div>';
        return;
    }

    let xhr = new XMLHttpRequest();
    xhr.open("POST", "", true);
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhr.onload = function(){
        if(xhr.responseText === "OK"){
            alertDiv.innerHTML = '<div class="alert alert-success">Uživatel přidán!</div>';
            let ul = document.getElementById('userList');
            let li = document.createElement('li');
            li.className = 'list-group-item';
            li.textContent = ul.children.length + 1 + ": " + name;
            ul.appendChild(li);
            document.getElementById('username').value = '';
        } else {
            alertDiv.innerHTML = '<div class="alert alert-danger">Chyba při přidávání uživatele!</div>';
        }
    };
    xhr.send("new_user=" + encodeURIComponent(name));
});
</script>

</body>
</html>
