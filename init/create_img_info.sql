CREATE TABLE img_info (
  posted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  img_id INT NOT NULL PRIMARY KEY,
  mime_type VARCHAR(255) NOT NULL,
  img_filename VARCHAR(255) UNIQUE NOT NULL,
  img_width INT,
  img_height INT
);
