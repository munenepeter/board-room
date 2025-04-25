-- --------------------------------------------------------
-- Host:                         C:\Users\Peter\laragon\www\board-room\database\dbv2.sqlite
-- Server version:               3.39.0
-- Server OS:                    
-- HeidiSQL Version:             12.1.0.6537
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES  */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for dbv2
CREATE DATABASE IF NOT EXISTS "dbv2";
;

-- Dumping structure for table dbv2.boardrooms
CREATE TABLE IF NOT EXISTS boardrooms (
    room_id INTEGER PRIMARY KEY AUTOINCREMENT,
    room_name TEXT NOT NULL UNIQUE,
    capacity INTEGER NOT NULL,
    location TEXT NOT NULL,
    amenities TEXT, -- JSON string of available amenities
    is_active BOOLEAN DEFAULT TRUE,
    thumbnail_url TEXT -- URL for room image
);

-- Data exporting was unselected.

-- Dumping structure for table dbv2.bookings
CREATE TABLE IF NOT EXISTS bookings (
    booking_id INTEGER PRIMARY KEY AUTOINCREMENT,
    room_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    event_name TEXT NOT NULL,
    start_time DATETIME NOT NULL,
    end_time DATETIME NOT NULL,
    attendees_count INTEGER NOT NULL,
    status TEXT DEFAULT 'Pending', -- Pending, Approved, Rejected, Cancelled, Completed
    approver_id INTEGER,
    approval_notes TEXT,
    google_calendar_event_id TEXT, -- For calendar integration
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (room_id) REFERENCES boardrooms(room_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (approver_id) REFERENCES users(user_id)
);

-- Data exporting was unselected.

-- Dumping structure for table dbv2.booking_details
CREATE TABLE IF NOT EXISTS booking_details (
    detail_id INTEGER PRIMARY KEY AUTOINCREMENT,
    booking_id INTEGER NOT NULL,
    description TEXT,
    requirements TEXT, -- Special requirements for the meeting
    FOREIGN KEY (booking_id) REFERENCES bookings(booking_id)
);

-- Data exporting was unselected.

-- Dumping structure for table dbv2.calendar_sync
CREATE TABLE IF NOT EXISTS calendar_sync (
    sync_id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    provider TEXT NOT NULL, -- 'google', 'outlook', etc.
    access_token TEXT NOT NULL,
    refresh_token TEXT,
    expiry_date DATETIME,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- Data exporting was unselected.

-- Dumping structure for table dbv2.departments
CREATE TABLE IF NOT EXISTS departments (
    department_id INTEGER PRIMARY KEY AUTOINCREMENT,
    department_name TEXT NOT NULL UNIQUE
);

-- Data exporting was unselected.

-- Dumping structure for table dbv2.notifications
CREATE TABLE IF NOT EXISTS notifications (
    notification_id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    title TEXT NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    related_entity_type TEXT NOT NULL, -- 'booking', 'system', etc.
    related_entity_id INTEGER, -- ID of the related entity
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- Data exporting was unselected.

-- Dumping structure for table dbv2.reports
CREATE TABLE IF NOT EXISTS reports (
    report_id INTEGER PRIMARY KEY AUTOINCREMENT,
    report_name TEXT NOT NULL,
    report_description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Data exporting was unselected.

-- Dumping structure for table dbv2.room_suggestions
CREATE TABLE IF NOT EXISTS room_suggestions (
    suggestion_id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    attendees_count INTEGER NOT NULL,
    requested_time DATETIME NOT NULL,
    duration_minutes INTEGER NOT NULL,
    suggested_room_id INTEGER,
    is_accepted BOOLEAN,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (suggested_room_id) REFERENCES boardrooms(room_id)
);

-- Data exporting was unselected.

-- Dumping structure for table dbv2.users
CREATE TABLE IF NOT EXISTS users (
    user_id INTEGER PRIMARY KEY AUTOINCREMENT,
    first_name TEXT NOT NULL,
    last_name TEXT NOT NULL,
    email TEXT NOT NULL UNIQUE,
    password TEXT NOT NULL, -- For login authentication
    department_id INTEGER NOT NULL,
    role TEXT DEFAULT 'user', -- 'user', 'approver', or 'admin'
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(department_id)
);

-- Data exporting was unselected.

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
