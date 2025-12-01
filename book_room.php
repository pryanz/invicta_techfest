<?php
session_start();
require 'includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'participant') {
    header("Location: index.php");
    exit();
}

if (isset($_POST['book_room'])) {
    $p_id = $_SESSION['user_id'];
    $type_id = $_POST['type_id'];
    $checkin = $_POST['checkin'];
    $checkout = $_POST['checkout'];

    // Start Transaction (Crucial so two people don't grab the last bed at once)
    $conn->begin_transaction();

    try {
        // 1. Double Check: Does user already have a booking?
        $chk = $conn->query("SELECT booking_id FROM bookings WHERE participant_id = $p_id");
        if($chk->num_rows > 0) {
            throw new Exception("You already have a booking!");
        }

        // 2. FIND AVAILABLE ROOM (The Core Logic)
        // Find the first room of this type where occupancy is LESS than capacity
        $sql_find = "SELECT a.room_id, a.current_occupancy, rt.capacity 
                    FROM accommodation a
                    JOIN room_types rt ON a.type_id = rt.type_id
                    WHERE a.type_id = ? AND a.current_occupancy < rt.capacity
                    LIMIT 1 FOR UPDATE"; 
                     // 'FOR UPDATE' locks the row so no one else writes to it until we are done
        
        $stmt = $conn->prepare($sql_find);
        $stmt->bind_param("i", $type_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $room_id = $row['room_id'];
            
            // 3. BOOK THE ROOM
            $stmt_book = $conn->prepare("INSERT INTO bookings (participant_id, room_id, checkin_date, checkout_date) VALUES (?, ?, ?, ?)");
            $stmt_book->bind_param("iiss", $p_id, $room_id, $checkin, $checkout);
            $stmt_book->execute();

            // 4. INCREASE OCCUPANCY
            $stmt_upd = $conn->query("UPDATE accommodation SET current_occupancy = current_occupancy + 1 WHERE room_id = $room_id");

            $conn->commit(); // Save everything
            echo "<script>alert('Success! Room Allocated.'); window.location.href='accommodation.php';</script>";

        } else {
            throw new Exception("Sorry! All rooms of this type are fully booked.");
        }

    } catch (Exception $e) {
        $conn->rollback(); // Undo changes if something went wrong
        echo "<script>alert('Error: " . $e->getMessage() . "'); window.location.href='accommodation.php';</script>";
    }
}
?>