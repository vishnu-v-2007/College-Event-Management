<?php
/**
 * College Event Management System
 * Event Registration Processor (student/event_register.php)
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

requireStudentLogin('../login.php?redirect=events.php');

$student = getLoggedInStudent();
$pdo = getDBConnection();

$eventId = (int)($_REQUEST['event_id'] ?? 0);

if ($eventId <= 0) {
    setFlash('danger', 'Invalid event selected for registration.');
    header('Location: ../events.php');
    exit();
}

try {
    // 1. Fetch event
    $stmt = $pdo->prepare("SELECT * FROM events WHERE event_id = ?");
    $stmt->execute([$eventId]);
    $event = $stmt->fetch();

    if (!$event) {
        setFlash('danger', 'The requested event could not be found.');
        header('Location: ../events.php');
        exit();
    }

    // 2. Check if student already registered
    $checkStmt = $pdo->prepare("SELECT registration_id FROM registrations WHERE student_id = ? AND event_id = ?");
    $checkStmt->execute([$student['student_id'], $eventId]);
    if ($checkStmt->fetch()) {
        setFlash('warning', 'You are already registered for "' . $event['event_name'] . '". Your pass is available below.');
        header('Location: my_events.php');
        exit();
    }

    // 3. Check seat availability
    if ((int)$event['available_seats'] <= 0) {
        setFlash('danger', 'Registration Closed: All seats for "' . $event['event_name'] . '" are currently booked.');
        header('Location: ../events.php');
        exit();
    }

    // 4. Begin transaction
    $pdo->beginTransaction();

    $regStmt = $pdo->prepare("INSERT INTO registrations (student_id, event_id, status) VALUES (?, ?, 'Approved')");
    $regStmt->execute([$student['student_id'], $eventId]);

    $seatStmt = $pdo->prepare("UPDATE events SET available_seats = available_seats - 1 WHERE event_id = ? AND available_seats > 0");
    $seatStmt->execute([$eventId]);

    $pdo->commit();

    setFlash('success', 'Registration Confirmed! Your seat for "' . $event['event_name'] . '" has been reserved.');
    header('Location: my_events.php');
    exit();
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    setFlash('danger', 'Registration error: ' . $e->getMessage());
    header('Location: ../events.php');
    exit();
}

