-- database: c:\Users\Peter\laragon\www\board-room\database\database.sqlite

-- Table: Users (replaces Employees)
INSERT INTO departments (department_name) VALUES ('Engineering');
INSERT INTO departments (department_name) VALUES ('Marketing');
INSERT INTO departments (department_name) VALUES ('Human Resources');



-- Passwords are hashed using a secure hashing algorithm (e.g., bcrypt) in a real system.
-- For simplicity, plaintext passwords are used here.
INSERT INTO users (first_name, last_name, email, password, department_id)
VALUES ('John', 'Doe', 'john.doe@example.com', '$2y$10$pJwuUF0LHnOvZ6ygCoI4iOmSBqq/MDCFAJvPJdvKfHINvrR06ZOrC', 1);

INSERT INTO users (first_name, last_name, email, password, department_id)
VALUES ('Jane', 'Smith', 'jane.smith@example.com', '$2y$10$pJwuUF0LHnOvZ6ygCoI4iOmSBqq/MDCFAJvPJdvKfHINvrR06ZOrC', 2);

INSERT INTO users (first_name, last_name, email, password, department_id)
VALUES ('Alice', 'Johnson', 'alice.johnson@example.com', '$2y$10$pJwuUF0LHnOvZ6ygCoI4iOmSBqq/MDCFAJvPJdvKfHINvrR06ZOrC', 3);


INSERT INTO boardrooms (room_name, capacity, location)
VALUES ('Room A', 10, 'Floor 1');

INSERT INTO boardrooms (room_name, capacity, location)
VALUES ('Room B', 15, 'Floor 2');

INSERT INTO boardrooms (room_name, capacity, location)
VALUES ('Room C', 20, 'Floor 3');



INSERT INTO bookings (room_id, user_id, event_name, start_time, end_time)
VALUES (1, 1, 'Project Kickoff', '2025-01-25 09:00:00', '2025-01-25 10:00:00');

INSERT INTO bookings (room_id, user_id, event_name, start_time, end_time)
VALUES (2, 2, 'Marketing Strategy Meeting', '2025-01-26 14:00:00', '2025-01-26 15:30:00');

INSERT INTO bookings (room_id, user_id, event_name, start_time, end_time)
VALUES (3, 3, 'HR Policy Review', '2025-01-27 11:00:00', '2025-01-27 12:00:00');



INSERT INTO events (booking_id, event_description, attendees_count)
VALUES (1, 'Discuss project scope and timelines.', 8);

INSERT INTO events (booking_id, event_description, attendees_count)
VALUES (2, 'Plan Q4 marketing campaigns.', 12);

INSERT INTO events (booking_id, event_description, attendees_count)
VALUES (3, 'Review and update HR policies.', 5);

UPDATE users SET role = 'admin' WHERE user_id = 1;
