<?php
session_start();
require_once 'db_config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Create Contact
if (isset($_POST['create'])) {
    $name = $mysqli->real_escape_string($_POST['name']);
    $email = $mysqli->real_escape_string($_POST['email']);
    $phone = $mysqli->real_escape_string($_POST['phone']);
    $user_id = $_SESSION['user_id'];

    $stmt = $mysqli->prepare("INSERT INTO contacts (user_id, name, email, phone) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $name, $email, $phone);
    $stmt->execute();
    $stmt->close();
}

// Update Contact
if (isset($_POST['update'])) {
    $id = $mysqli->real_escape_string($_POST['contact_id']);
    $name = $mysqli->real_escape_string($_POST['name']);
    $email = $mysqli->real_escape_string($_POST['email']);
    $phone = $mysqli->real_escape_string($_POST['phone']);
    $user_id = $_SESSION['user_id'];

    $stmt = $mysqli->prepare("UPDATE contacts SET name = ?, email = ?, phone = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param("sssii", $name, $email, $phone, $id, $user_id);
    $stmt->execute();
    $stmt->close();
}

// Delete Contact
if (isset($_GET['delete'])) {
    $id = $mysqli->real_escape_string($_GET['delete']);
    $user_id = $_SESSION['user_id'];

    $stmt = $mysqli->prepare("DELETE FROM contacts WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $id, $user_id);
    $stmt->execute();
    $stmt->close();
}

// Fetch Contacts
$stmt = $mysqli->prepare("SELECT * FROM contacts WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$contacts = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html>

<head>
    <title>Contact List</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <div class="container">
        <h2>Contact Management</h2>
        <p>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!
            <a href="logout.php" class="logout-btn">Logout</a>
        </p>

        <!-- Add Contact Form -->
        <h3>Add New Contact</h3>
        <form method="post" action="">
            <input type="text" name="name" placeholder="Name" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="tel" name="phone" placeholder="Phone" required>
            <button type="submit" name="create">Add Contact</button>
        </form>

        <!-- Contact List -->
        <h3>Contacts</h3>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($contacts as $contact): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($contact['name']); ?></td>
                        <td><?php echo htmlspecialchars($contact['email']); ?></td>
                        <td><?php echo htmlspecialchars($contact['phone']); ?></td>
                        <td>
                            <a href="#" onclick="editContact(
                            <?php echo $contact['id']; ?>, 
                            '<?php echo htmlspecialchars($contact['name']); ?>', 
                            '<?php echo htmlspecialchars($contact['email']); ?>', 
                            '<?php echo htmlspecialchars($contact['phone']); ?>'
                        )">Edit</a>
                            <a href="?delete=<?php echo $contact['id']; ?>" onclick="return confirm('Are you sure?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Edit Contact Modal -->
        <div id="editModal" class="modal">
            <form method="post" action="" class="modal-content">
                <input type="hidden" name="contact_id" id="edit-id">
                <input type="text" name="name" id="edit-name" placeholder="Name" required>
                <input type="email" name="email" id="edit-email" placeholder="Email" required>
                <input type="tel" name="phone" id="edit-phone" placeholder="Phone" required>
                <button type="submit" name="update">Update Contact</button>
                <button type="button" onclick="closeModal()">Cancel</button>
            </form>
        </div>
    </div>

    <script>
        function editContact(id, name, email, phone) {
            document.getElementById('edit-id').value = id;
            document.getElementById('edit-name').value = name;
            document.getElementById('edit-email').value = email;
            document.getElementById('edit-phone').value = phone;
            document.getElementById('editModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('editModal').style.display = 'none';
        }
    </script>
</body>

</html>