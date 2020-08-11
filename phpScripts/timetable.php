<?php

$conn = include('./helpers/db_connection.php');

$sql = "SELECT * FROM classroom_plan";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
        echo "id: " . $row["id"]. "|| Day: " . $row["day_name"]. " || Start Time" . $row["start_time"]. " || Server Path".$row["server_path"]."<br>";
    }
} else {
    echo "0 results";
}
$conn->close();