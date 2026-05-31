CREATE DATABASE IF NOT EXISTS crud_mahasiswa
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE crud_mahasiswa;

CREATE TABLE IF NOT EXISTS mahasiswa (
    id       INT          AUTO_INCREMENT PRIMARY KEY,
    nim      VARCHAR(20)  NOT NULL UNIQUE,
    nama     VARCHAR(100) NOT NULL,
    jurusan  VARCHAR(100) NOT NULL,
    angkatan VARCHAR(4)   NOT NULL,
    ipk      DECIMAL(3,2) NOT NULL DEFAULT 0.00,
    created_at TIMESTAMP  DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP  DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data contoh
INSERT INTO mahasiswa (nim, nama, jurusan, angkatan, ipk) VALUES
('2021001', 'Budi Santoso',    'Teknik Informatika', '2021', 3.75),
('2021002', 'Siti Rahayu',     'Sistem Informasi',   '2021', 3.60),
('2022001', 'Ahmad Fauzi',     'Teknik Informatika', '2022', 3.45),
('2022002', 'Dewi Lestari',    'Manajemen Informatika','2022', 3.80),
('2023001', 'Rizky Pratama',   'Teknik Informatika', '2023', 3.55);
