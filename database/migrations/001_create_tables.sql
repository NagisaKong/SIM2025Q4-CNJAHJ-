CREATE TABLE profiles (
    id INTEGER PRIMARY KEY AUTO_INCREMENT,
    role VARCHAR(50) NOT NULL,
    description TEXT NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'active',
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL
);

CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTO_INCREMENT,
    profile_id INTEGER NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'active',
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    FOREIGN KEY (profile_id) REFERENCES profiles(id)
);

CREATE TABLE service_categories (
    id INTEGER PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(120) NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'active',
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL
);

CREATE TABLE pin_requests (
    id INTEGER PRIMARY KEY AUTO_INCREMENT,
    pin_id INTEGER NOT NULL,
    category_id INTEGER NOT NULL,
    title VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    location VARCHAR(150) NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'open',
    requested_date DATE NOT NULL,
    views_count INTEGER NOT NULL DEFAULT 0,
    shortlist_count INTEGER NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    FOREIGN KEY (pin_id) REFERENCES users(id),
    FOREIGN KEY (category_id) REFERENCES service_categories(id)
);

CREATE TABLE shortlists (
    id INTEGER PRIMARY KEY AUTO_INCREMENT,
    csr_id INTEGER NOT NULL,
    request_id INTEGER NOT NULL,
    created_at DATETIME NOT NULL,
    UNIQUE KEY uniq_shortlist (csr_id, request_id),
    FOREIGN KEY (csr_id) REFERENCES users(id),
    FOREIGN KEY (request_id) REFERENCES pin_requests(id)
);

CREATE TABLE matches (
    id INTEGER PRIMARY KEY AUTO_INCREMENT,
    csr_id INTEGER NOT NULL,
    request_id INTEGER NOT NULL,
    status VARCHAR(20) NOT NULL,
    matched_at DATETIME NOT NULL,
    completed_at DATETIME NULL,
    FOREIGN KEY (csr_id) REFERENCES users(id),
    FOREIGN KEY (request_id) REFERENCES pin_requests(id)
);

CREATE TABLE audit_logs (
    id INTEGER PRIMARY KEY AUTO_INCREMENT,
    user_id INTEGER NOT NULL,
    action VARCHAR(120) NOT NULL,
    payload TEXT,
    created_at DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
