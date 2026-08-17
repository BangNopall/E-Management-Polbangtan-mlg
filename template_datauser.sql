-- Template Import Data User (Mahasiswa) Terbaru
-- Sesuai dengan format fitur import Excel: NIM, Nama, ID Prodi, Email
-- Password default sama seperti sebelumnya (di-hash)
-- role_id = 3 (Mahasiswa), status = 'didalam'
-- Jika email kosong, isi dengan nim@dummy.com

INSERT INTO users (nim, name, prodi_id, email, password, role_id, status) VALUES 
('0123456789', 'Budi Santoso', 1, 'budi@example.com', '$2a$12$UAh8ZDvx8RKru5GqD1CYNe6H7Sg9TTV2Xbd3KYuP6Be/meKRgGsc.', '3', 'didalam'),
('0123456790', 'Siti Aminah', 2, '0123456790@dummy.com', '$2a$12$UAh8ZDvx8RKru5GqD1CYNe6H7Sg9TTV2Xbd3KYuP6Be/meKRgGsc.', '3', 'didalam'),
('0123456791', 'Andi Dharma', 1, '0123456791@dummy.com', '$2a$12$UAh8ZDvx8RKru5GqD1CYNe6H7Sg9TTV2Xbd3KYuP6Be/meKRgGsc.', '3', 'didalam'),
('0123456792', 'Rini Puspita', 3, 'rini.puspita@example.com', '$2a$12$UAh8ZDvx8RKru5GqD1CYNe6H7Sg9TTV2Xbd3KYuP6Be/meKRgGsc.', '3', 'didalam'),
('0123456793', 'Joko Susanto', 2, '0123456793@dummy.com', '$2a$12$UAh8ZDvx8RKru5GqD1CYNe6H7Sg9TTV2Xbd3KYuP6Be/meKRgGsc.', '3', 'didalam');
