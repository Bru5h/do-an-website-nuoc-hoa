<?php
session_start();
include 'db.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data['id'])) {
        $id = $data['id'];

        try {
            $stmt = $pdo->prepare("DELETE FROM lienhe WHERE idlienhe = :id");
            $stmt->execute(['id' => $id]);

            echo json_encode(['success' => true]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Missing contact ID']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
