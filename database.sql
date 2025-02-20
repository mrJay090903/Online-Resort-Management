-- Create users table
CREATE TABLE users (
  id INT PRIMARY KEY AUTO_INCREMENT,
  email VARCHAR(100) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  user_type ENUM('admin', 'staff', 'customer') NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
-- Create customers table
CREATE TABLE customers (
  id INT PRIMARY KEY AUTO_INCREMENT,
  user_id INT NOT NULL,
  full_name VARCHAR(100) NOT NULL,
  contact_number VARCHAR(20) NOT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
-- Create staff table
CREATE TABLE staff (
  id INT PRIMARY KEY AUTO_INCREMENT,
  user_id INT NOT NULL,
  staff_name VARCHAR(100) NOT NULL,
  contact_number VARCHAR(20) NOT NULL,
  position VARCHAR(50) NOT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
-- Create feedbacks table
CREATE TABLE feedbacks (
  id INT PRIMARY KEY AUTO_INCREMENT,
  customer_id INT NOT NULL,
  rating INT NOT NULL,
  message TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
  FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
);
-- Check and create rooms table if not exists
CREATE TABLE IF NOT EXISTS rooms (
  id INT PRIMARY KEY AUTO_INCREMENT,
  room_name VARCHAR(100) NOT NULL,
  description TEXT NOT NULL,
  capacity INT NOT NULL,
  base_price DECIMAL(10, 2) NOT NULL,
  day_price DECIMAL(10, 2) NOT NULL,
  night_price DECIMAL(10, 2) NOT NULL,
  picture VARCHAR(255) NOT NULL,
  status ENUM('available', 'occupied', 'maintenance') DEFAULT 'available'
);
-- Check and create venues table if not exists
CREATE TABLE IF NOT EXISTS venues (
  id INT PRIMARY KEY AUTO_INCREMENT,
  type ENUM('cottage', 'hall') NOT NULL,
  name VARCHAR(100) NOT NULL,
  description TEXT NOT NULL,
  capacity INT NOT NULL,
  price DECIMAL(10, 2) NOT NULL,
  picture VARCHAR(255) NOT NULL,
  status ENUM('available', 'occupied', 'maintenance') DEFAULT 'available'
);
-- Check and create bookings table if not exists
CREATE TABLE IF NOT EXISTS bookings (
  id INT PRIMARY KEY AUTO_INCREMENT,
  customer_id INT NOT NULL,
  check_in_date DATE NOT NULL,
  check_out_date DATE NOT NULL,
  total_guests INT NOT NULL,
  total_amount DECIMAL(10, 2) NOT NULL,
  status ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
);
-- Check and create booking_rooms table if not exists
CREATE TABLE IF NOT EXISTS booking_rooms (
  booking_id INT NOT NULL,
  room_id INT NOT NULL,
  time_slot ENUM('day', 'night') NOT NULL,
  quantity INT NOT NULL,
  price_per_night DECIMAL(10, 2) NOT NULL,
  PRIMARY KEY (booking_id, room_id),
  FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
  FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE
);
-- Check and create booking_venues table if not exists
CREATE TABLE IF NOT EXISTS booking_venues (
  booking_id INT NOT NULL,
  venue_id INT NOT NULL,
  price DECIMAL(10, 2) NOT NULL,
  PRIMARY KEY (booking_id, venue_id),
  FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
  FOREIGN KEY (venue_id) REFERENCES venues(id) ON DELETE CASCADE
);
-- Add new columns to rooms table if they don't exist
ALTER TABLE rooms
ADD COLUMN IF NOT EXISTS description TEXT NOT NULL
AFTER room_name,
  ADD COLUMN IF NOT EXISTS capacity INT NOT NULL
AFTER description,
  ADD COLUMN IF NOT EXISTS base_price DECIMAL(10, 2) NOT NULL
AFTER capacity,
  ADD COLUMN IF NOT EXISTS day_price DECIMAL(10, 2) NOT NULL
AFTER base_price,
  ADD COLUMN IF NOT EXISTS night_price DECIMAL(10, 2) NOT NULL
AFTER day_price,
  ADD COLUMN IF NOT EXISTS picture VARCHAR(255) NOT NULL
AFTER night_price,
  ADD COLUMN IF NOT EXISTS status ENUM('available', 'occupied', 'maintenance') DEFAULT 'available'
AFTER picture;
-- Add new columns to venues table if they don't exist
ALTER TABLE venues
ADD COLUMN IF NOT EXISTS type ENUM('cottage', 'hall') NOT NULL FIRST,
  ADD COLUMN IF NOT EXISTS description TEXT NOT NULL
AFTER name,
  ADD COLUMN IF NOT EXISTS capacity INT NOT NULL
AFTER description,
  ADD COLUMN IF NOT EXISTS price DECIMAL(10, 2) NOT NULL
AFTER capacity,
  ADD COLUMN IF NOT EXISTS picture VARCHAR(255) NOT NULL
AFTER price,
  ADD COLUMN IF NOT EXISTS status ENUM('available', 'occupied', 'maintenance') DEFAULT 'available'
AFTER picture;
-- Insert sample data only if tables are empty
INSERT INTO rooms (
    room_name,
    description,
    capacity,
    base_price,
    day_price,
    night_price,
    picture,
    status
  )
SELECT *
FROM (
    SELECT 'Deluxe Room',
      'Spacious room with mountain view',
      2,
      2500.00,
      1500.00,
      2000.00,
      'deluxe-room.jpg',
      'available'
    UNION ALL
    SELECT 'Family Suite',
      'Perfect for families, includes kitchen',
      4,
      4500.00,
      2500.00,
      3500.00,
      'family-suite.jpg',
      'available'
    UNION ALL
    SELECT 'Executive Room',
      'Luxury room with premium amenities',
      2,
      3500.00,
      2000.00,
      2800.00,
      'executive-room.jpg',
      'available'
    UNION ALL
    SELECT 'Grand Suite',
      'Our most luxurious accommodation',
      6,
      6500.00,
      4000.00,
      5500.00,
      'grand-suite.jpg',
      'available'
  ) AS tmp
WHERE NOT EXISTS (
    SELECT 1
    FROM rooms
    LIMIT 1
  );
-- Insert sample venues data only if table is empty
INSERT INTO venues (
    type,
    name,
    description,
    capacity,
    price,
    picture,
    status
  )
SELECT *
FROM (
    SELECT 'cottage',
      'Garden Cottage',
      'Perfect for day trips and picnics',
      8,
      1500.00,
      'garden-cottage.jpg',
      'available'
    UNION ALL
    SELECT 'cottage',
      'Pool Cottage',
      'Located near the infinity pool',
      10,
      2000.00,
      'pool-cottage.jpg',
      'available'
    UNION ALL
    SELECT 'hall',
      'Grand Hall',
      'Perfect for events and celebrations',
      100,
      15000.00,
      'grand-hall.jpg',
      'available'
    UNION ALL
    SELECT 'hall',
      'Conference Hall',
      'Ideal for business meetings',
      50,
      10000.00,
      'conference-hall.jpg',
      'available'
  ) AS tmp
WHERE NOT EXISTS (
    SELECT 1
    FROM venues
    LIMIT 1
  );
-- Create booking_rooms table if not exists
CREATE TABLE IF NOT EXISTS booking_rooms (
  booking_id INT NOT NULL,
  room_id INT NOT NULL,
  time_slot ENUM('day', 'night') NOT NULL,
  quantity INT NOT NULL,
  price_per_night DECIMAL(10, 2) NOT NULL,
  PRIMARY KEY (booking_id, room_id),
  FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
  FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE
);
-- Create booking_venues table if not exists
CREATE TABLE IF NOT EXISTS booking_venues (
  booking_id INT NOT NULL,
  venue_id INT NOT NULL,
  price DECIMAL(10, 2) NOT NULL,
  PRIMARY KEY (booking_id, venue_id),
  FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
  FOREIGN KEY (venue_id) REFERENCES venues(id) ON DELETE CASCADE
);
-- Add features table if not exists
CREATE TABLE IF NOT EXISTS features (
  id INT PRIMARY KEY AUTO_INCREMENT,
  title VARCHAR(255) NOT NULL,
  description TEXT NOT NULL,
  image VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);