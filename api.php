<?php
$input = json_decode(file_get_contents('php://input'), true);
$data = $input ? $input : $_POST;

$name = trim($data['name'] ?? '');
$email = trim($data['email'] ?? '');
$message = trim($data['message'] ?? '');

$errors = [];
if (empty($name)) $errors[] = 'Имя обязательно';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Некорректный Email';

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'errors' => $errors]);
    exit;
}

if ($method === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        $login = 'user_' . bin2hex(random_bytes(4));
        $password = bin2hex(random_bytes(4));
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO users (login, password_hash) VALUES (?, ?)");
        $stmt->execute([$login, $passwordHash]);
        $userId = $pdo->lastInsertId();
        
        $_SESSION['user_id'] = $userId;
        $_SESSION['login'] = $login;
    } else {
        $userId = $_SESSION['user_id'];
        $login = $_SESSION['login'];
        $password = "Уже сгенерирован";
    }

    $stmt = $pdo->prepare("INSERT INTO form_data (user_id, name, email, message) VALUES (?, ?, ?, ?)");
    $stmt->execute([$userId, $name, $email, $message]);

    echo json_encode([
        'status' => 'success',
        'message' => 'Данные сохранены',
        'credentials' => [
            'login' => $login,
            'password' => $password ?? null,
            'profile_url' => '/profile/' . $login
        ]
    ]);
} elseif ($method === 'PUT') {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Необходима авторизация']);
        exit;
    }

    $stmt = $pdo->prepare("UPDATE form_data SET name = ?, email = ?, message = ? WHERE user_id = ?");
    $stmt->execute([$name, $email, $message, $_SESSION['user_id']]);

    echo json_encode(['status' => 'success', 'message' => 'Данные успешно обновлены']);
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Метод не разрешен']);
}