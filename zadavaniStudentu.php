<?php
// Připojení k databázi
$host = 'localhost';
$dbname = 'student_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Chyba připojení: " . $e->getMessage());
}

// Zpracování formulářů
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? $_GET['id'] : null;

if ($action == 'add' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $year = $_POST['year'];
    $program = $_POST['program'];

    $stmt = $pdo->prepare("INSERT INTO students (name, year, program) VALUES (?, ?, ?)");
    $stmt->execute([$name, $year, $program]);
    echo "Student přidán úspěšně!";
    $action = 'list';
}

if ($action == 'edit' && $id && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $year = $_POST['year'];
    $program = $_POST['program'];

    $stmt = $pdo->prepare("UPDATE students SET name = ?, year = ?, program = ? WHERE id = ?");
    $stmt->execute([$name, $year, $program, $id]);
    echo "Student úspěšně aktualizován!";
    $action = 'list';
}

// Výběr studenta pro zobrazení detailu nebo úpravy
if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
    $stmt->execute([$id]);
    $student = $stmt->fetch();
}

// Hlavní přepínač akcí
switch ($action) {
    case 'add':
        ?>
        <h2>Přidat studenta</h2>
        <form method="POST">
            Jméno: <input type="text" name="name" required><br>
            Ročník: <input type="number" name="year" required><br>
            Program: <input type="text" name="program" required><br>
            <input type="submit" value="Přidat studenta">
        </form>
        <?php
        break;
    case 'edit':
        if ($student) {
            ?>
            <h2>Upravit studenta</h2>
            <form method="POST">
                Jméno: <input type="text" name="name" value="<?= htmlspecialchars($student['name']) ?>" required><br>
                Ročník: <input type="number" name="year" value="<?= htmlspecialchars($student['year']) ?>" required><br>
                Program: <input type="text" name="program" value="<?= htmlspecialchars($student['program']) ?>" required><br>
                <input type="submit" value="Upravit studenta">
            </form>
            <?php
        } else {
            echo "Student nenalezen.";
        }
        break;
    case 'detail':
        if ($student) {
            ?>
            <h2>Detail studenta</h2>
            <p>Jméno: <?= htmlspecialchars($student['name']) ?></p>
            <p>Ročník: <?= htmlspecialchars($student['year']) ?></p>
            <p>Program: <?= htmlspecialchars($student['program']) ?></p>
            <a href="?action=list">Zpět na seznam</a>
            <?php
        } else {
            echo "Student nenalezen.";
        }
        break;
    case 'list':
    default:
        $stmt = $pdo->query("SELECT * FROM students");
        $students = $stmt->fetchAll();
        ?>
        <h2>Seznam studentů</h2>
        <a href="?action=add">Přidat nového studenta</a>
        <ul>
            <?php foreach ($students as $student): ?>
                <li>
                    <a href="?action=detail&id=<?= $student['id'] ?>"><?= htmlspecialchars($student['name']) ?></a>
                    - <a href="?action=edit&id=<?= $student['id'] ?>">Upravit</a>
                </li>
            <?php endforeach; ?>
        </ul>
        <?php
        break;
}
?>
