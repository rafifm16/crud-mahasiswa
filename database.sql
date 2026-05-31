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
('2001001', 'Rafi',    'Teknik Informatika', '2001', 3.90),
('2002002', 'Fadhil',     'Manajemen Informatika',   '2002', 3.80),
('2003001', 'Mubarok',     'Teknik Komputer', '2003', 3.45),
('2004002', 'Dimas',    'Sistem Informasi','2004', 3.60),
('2005001', 'Imam',   'Cyber Security', '2005', 3.54);
