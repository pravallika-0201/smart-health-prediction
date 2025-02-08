<?php
// Include database configuration
include('config/database.php');

// Retrieve the ID from the URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch doctor data from the database for the specific ID
$query = "SELECT * FROM doctors WHERE id = $id";
$result = $conn->query($query);

// Check if the doctor is found
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo '<table border="1">';
    echo '<tr><th>ID</th><th>Name</th><th>Specialization</th><th>Contact</th></tr>';
    echo '<tr>';
    echo '<td>' . $row['id'] . '</td>';
    echo '<td>' . $row['name'] . '</td>';
    echo '<td>' . $row['specialization'] . '</td>';
    echo '<td>' . $row['contact'] . '</td>';
    echo '</tr>';
    echo '</table>';
} else {
    echo "No doctor found with the provided ID.";
}

// Close the database connection
$conn->close();
?>
