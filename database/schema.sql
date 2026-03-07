
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50),
    password VARCHAR(255)
);

CREATE TABLE workers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100)
);

CREATE TABLE jobs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    description TEXT
);

CREATE TABLE assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    job_id INT,
    worker_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
