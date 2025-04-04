-- Table: Users (replaces Employees)
CREATE TABLE users (
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

-- Table: Departments
CREATE TABLE departments (
    department_id INTEGER PRIMARY KEY AUTOINCREMENT,
    department_name TEXT NOT NULL UNIQUE
);

-- Table: BoardRooms (enhanced with more details)
CREATE TABLE boardrooms (
    room_id INTEGER PRIMARY KEY AUTOINCREMENT,
    room_name TEXT NOT NULL UNIQUE,
    capacity INTEGER NOT NULL,
    location TEXT NOT NULL,
    amenities TEXT, -- JSON string of available amenities
    is_active BOOLEAN DEFAULT TRUE,
    thumbnail_url TEXT -- URL for room image
);

-- Table: Bookings (enhanced with approval workflow)
CREATE TABLE bookings (
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

-- Table: Events (renamed to BookingDetails for clarity)
CREATE TABLE booking_details (
    detail_id INTEGER PRIMARY KEY AUTOINCREMENT,
    booking_id INTEGER NOT NULL,
    description TEXT,
    requirements TEXT, -- Special requirements for the meeting
    FOREIGN KEY (booking_id) REFERENCES bookings(booking_id)
);

-- Table: Notifications
CREATE TABLE notifications (
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

-- Table: CalendarSync (for users who want to sync with external calendars)
CREATE TABLE calendar_sync (
    sync_id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    provider TEXT NOT NULL, -- 'google', 'outlook', etc.
    access_token TEXT NOT NULL,
    refresh_token TEXT,
    expiry_date DATETIME,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- Table: RoomSuggestions (logs suggestions for analytics)
CREATE TABLE room_suggestions (
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

-- Table: Reports
CREATE TABLE reports (
    report_id INTEGER PRIMARY KEY AUTOINCREMENT,
    report_name TEXT NOT NULL,
    report_description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Create indexes for performance
CREATE INDEX idx_bookings_room_time ON bookings(room_id, start_time, end_time);
CREATE INDEX idx_bookings_user ON bookings(user_id);
CREATE INDEX idx_notifications_user ON notifications(user_id);