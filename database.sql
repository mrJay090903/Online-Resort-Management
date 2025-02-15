CREATE TABLE feedbacks (
  id INT PRIMARY KEY AUTO_INCREMENT,
  customer_id INT NOT NULL,
  rating INT NOT NULL,
  message TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
  FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
);
ALTER TABLE customers DROP COLUMN profile_picture;