-- Demo data. Import after database.sql.
-- Logins:  warden@hostel.test  / Warden@1234   (warden)
--          student@hostel.test / Student@1234  (student; the other students use the same password)
USE hostel_booking;

INSERT INTO users (id, name, email, password, role) VALUES
(1, 'Grace Wanjiru', 'warden@hostel.test',  '$2y$10$oSVtd5vqjUIK5I/zS.HCzePvRddK1aQBYV8pp941niQiGKfc0h0Hm', 'warden'),
(2, 'Brian Otieno',  'student@hostel.test', '$2y$10$SYPq4.Vy/dP7klPGllonVO3etGwGf.8.7iHSJS.hc5xT90Z45w87e', 'student'),
(3, 'Faith Achieng', 'faith@hostel.test',   '$2y$10$SYPq4.Vy/dP7klPGllonVO3etGwGf.8.7iHSJS.hc5xT90Z45w87e', 'student'),
(4, 'Kevin Mwangi',  'kevin@hostel.test',   '$2y$10$SYPq4.Vy/dP7klPGllonVO3etGwGf.8.7iHSJS.hc5xT90Z45w87e', 'student'),
(5, 'Mercy Chebet',  'mercy@hostel.test',   '$2y$10$SYPq4.Vy/dP7klPGllonVO3etGwGf.8.7iHSJS.hc5xT90Z45w87e', 'student');

INSERT INTO hostels (id, name, location, gender, description) VALUES
(1, 'Mt Kenya Hall',  'Main Campus, Block A', 'male',   'Close to the library and the dining hall.'),
(2, 'Nyandarua Hall', 'Main Campus, Block C', 'female', 'Quiet hostel with a study room on every floor.'),
(3, 'Tana Court',     'Town Campus',          'mixed',  'Self-contained rooms near the town campus.');

INSERT INTO rooms (id, hostel_id, room_number, capacity, price_per_semester) VALUES
(1, 1, 'A101', 2,  8500.00),
(2, 1, 'A102', 4,  6500.00),
(3, 1, 'A103', 1, 12000.00),
(4, 2, 'C201', 2,  8500.00),
(5, 2, 'C202', 3,  7000.00),
(6, 3, 'T01',  1, 15000.00),
(7, 3, 'T02',  2, 11000.00);

-- Room A103 (capacity 1) is already full for Jan-Apr 2027, and Kevin has a pending
-- booking for it. Approving Kevin's booking is refused: that shows the capacity rule.
INSERT INTO bookings (user_id, room_id, semester, status) VALUES
(3, 3, 'Jan-Apr 2027', 'approved'),
(4, 3, 'Jan-Apr 2027', 'pending'),
(5, 4, 'Jan-Apr 2027', 'pending'),
(2, 2, 'Sep-Dec 2026', 'approved'),
(5, 5, 'Sep-Dec 2026', 'rejected'),
(4, 7, 'Sep-Dec 2026', 'cancelled');
