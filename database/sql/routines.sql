-- =====================================================
-- Daily Bun - Objek database programming
-- Jalankan di HeidiSQL (tab Query) SETELAH dml_ddl.sql dan views.sql.
-- Aman dijalankan ulang (DROP ... IF EXISTS di awal).
--
-- Tujuh objek (3 Function, 2 Stored Procedure, 2 Trigger):
--   1. FUNCTION  fn_hitung_subtotal(harga_satuan, jumlah)
--        Menghitung sub total satu baris detail pengadaan
--        (harga_satuan x jumlah). Dipakai saat menyimpan pengadaan.
--   2. FUNCTION  fn_hitung_ppn(subtotal, persen)
--        Menghitung nilai PPN dibulatkan (ROUND(subtotal * persen / 100)).
--        Dipakai saat menyimpan pengadaan dan penjualan.
--   3. FUNCTION  fn_stok_barang(idbarang)
--        Mengambil saldo stok terkini barang dari kartu_stok.
--        Dipakai di daftar barang dan pilihan barang penjualan.
--   4. PROCEDURE sp_aktifkan_margin_penjualan(idmargin)
--        Menonaktifkan margin yang sedang aktif, lalu mengaktifkan
--        margin yang dipilih. Dipakai tombol Aktifkan di /margin_penjualan.
--   5. PROCEDURE sp_ubah_status_barang(idbarang)
--        Mengubah status aktif/nonaktif barang (1 <-> 0).
--        Dipakai tombol ubah status di /barang.
--   6. TRIGGER   trg_detail_penerimaan_after_insert
--        Setiap detail penerimaan baru otomatis menambah stok
--        (kartu_stok jenis 'M').
--   7. TRIGGER   trg_detail_penjualan_after_insert
--        Setiap detail penjualan baru otomatis mengurangi stok
--        (kartu_stok jenis 'K').
--
-- Catatan:
-- - Tidak ada SQLSTATE/SIGNAL maupun percabangan IF di objek database;
--   validasi data dilakukan aplikasi di controller masing-masing.
-- - Penghapusan penerimaan (koreksi 'K') dan penghapusan penjualan
--   (pengembalian 'M') ditangani aplikasi di controller::destroy().
-- =====================================================
USE projek_pbd_094;

DROP TRIGGER   IF EXISTS trg_detail_penerimaan_after_insert;
DROP TRIGGER   IF EXISTS trg_detail_penjualan_after_insert;
DROP PROCEDURE IF EXISTS sp_aktifkan_margin_penjualan;
DROP PROCEDURE IF EXISTS sp_ubah_status_barang;
DROP FUNCTION  IF EXISTS fn_hitung_subtotal;
DROP FUNCTION  IF EXISTS fn_hitung_ppn;
DROP FUNCTION  IF EXISTS fn_stok_barang;

-- Sisa objek versi lama (bila masih ada).
DROP PROCEDURE IF EXISTS sp_hitung_ulang_total_pengadaan;
DROP PROCEDURE IF EXISTS sp_hapus_penerimaan;

DELIMITER $$

-- -----------------------------------------------------
-- 1. FUNCTION fn_hitung_subtotal
--    - Sub total = harga_satuan x jumlah.
--    - Nilai kosong/negatif dianggap 0 memakai CASE.
--    - DETERMINISTIC + NO SQL: hasil hanya bergantung pada parameter.
-- -----------------------------------------------------
CREATE FUNCTION fn_hitung_subtotal(p_harga INT, p_jumlah INT)
RETURNS INT
DETERMINISTIC
NO SQL
RETURN CASE WHEN p_harga > 0 THEN p_harga ELSE 0 END
     * CASE WHEN p_jumlah > 0 THEN p_jumlah ELSE 0 END$$

-- -----------------------------------------------------
-- 2. FUNCTION fn_hitung_ppn
--    - PPN = ROUND(subtotal x persen / 100).
--    - Nilai kosong/negatif dianggap 0 memakai CASE.
--    - DETERMINISTIC + NO SQL: hasil hanya bergantung pada parameter.
-- -----------------------------------------------------
CREATE FUNCTION fn_hitung_ppn(p_subtotal INT, p_persen INT)
RETURNS INT
DETERMINISTIC
NO SQL
RETURN ROUND(
    (CASE WHEN p_subtotal > 0 THEN p_subtotal ELSE 0 END)
  * (CASE WHEN p_persen > 0 THEN p_persen ELSE 0 END)
  / 100
)$$

-- -----------------------------------------------------
-- 3. FUNCTION fn_stok_barang
--    - Mengambil saldo stok terakhir barang dari kartu_stok.
--    - Mengembalikan 0 bila barang belum memiliki riwayat stok.
-- -----------------------------------------------------
CREATE FUNCTION fn_stok_barang(p_idbarang INT)
RETURNS INT
READS SQL DATA
RETURN COALESCE(
    (SELECT stock
       FROM kartu_stok
      WHERE idbarang = p_idbarang
      ORDER BY idkartu_stok DESC
      LIMIT 1),
    0
)$$

-- -----------------------------------------------------
-- 4. PROCEDURE sp_aktifkan_margin_penjualan
--    - Semua margin yang sedang aktif dinonaktifkan.
--    - Margin pilihan diaktifkan dan updated_at diperbarui.
--    - Hanya satu margin yang aktif setelah pemanggilan.
-- -----------------------------------------------------
CREATE PROCEDURE sp_aktifkan_margin_penjualan(IN p_idmargin INT)
BEGIN
    UPDATE margin_penjualan
       SET status = 0
     WHERE status = 1
       AND idmargin_penjualan <> p_idmargin;

    UPDATE margin_penjualan
       SET status = 1,
           updated_at = NOW()
     WHERE idmargin_penjualan = p_idmargin;
END$$

-- -----------------------------------------------------
-- 5. PROCEDURE sp_ubah_status_barang
--    - Mengubah status barang: 1 menjadi 0, selain 1 menjadi 1.
-- -----------------------------------------------------
CREATE PROCEDURE sp_ubah_status_barang(IN p_idbarang INT)
BEGIN
    UPDATE barang
       SET status = CASE WHEN status = 1 THEN 0 ELSE 1 END
     WHERE idbarang = p_idbarang;
END$$

-- -----------------------------------------------------
-- 6. TRIGGER trg_detail_penerimaan_after_insert
--    - Stok masuk otomatis saat detail penerimaan dibuat (jenis 'M').
--    - Baris barang dikunci (FOR UPDATE) agar saldo per barang berurutan.
--    - Satu-satunya penulis kartu_stok untuk penerimaan; aplikasi tidak
--      boleh menulis kartu_stok untuk penerimaan agar tidak dobel.
-- -----------------------------------------------------
CREATE TRIGGER trg_detail_penerimaan_after_insert
AFTER INSERT ON detail_penerimaan
FOR EACH ROW
BEGIN
    DECLARE v_kunci INT;
    DECLARE v_saldo INT DEFAULT 0;

    SELECT idbarang INTO v_kunci
      FROM barang
     WHERE idbarang = NEW.barang_idbarang
       FOR UPDATE;

    SELECT stock INTO v_saldo
      FROM kartu_stok
     WHERE idbarang = NEW.barang_idbarang
     ORDER BY idkartu_stok DESC
     LIMIT 1;

    INSERT INTO kartu_stok (jenis_transaksi, masuk, keluar, stock, created_at, idtransaksi, idbarang)
    VALUES ('M', NEW.jumlah_terima, 0, COALESCE(v_saldo, 0) + NEW.jumlah_terima, NOW(), NEW.idpenerimaan, NEW.barang_idbarang);
END$$

-- -----------------------------------------------------
-- 7. TRIGGER trg_detail_penjualan_after_insert
--    - Stok keluar otomatis saat detail penjualan dibuat (jenis 'K').
--    - Baris barang dikunci (FOR UPDATE) agar saldo per barang berurutan.
--    - Satu-satunya penulis kartu_stok untuk penjualan; aplikasi tidak
--      boleh menulis kartu_stok untuk penjualan agar tidak dobel.
-- -----------------------------------------------------
CREATE TRIGGER trg_detail_penjualan_after_insert
AFTER INSERT ON detail_penjualan
FOR EACH ROW
BEGIN
    DECLARE v_kunci INT;
    DECLARE v_saldo INT DEFAULT 0;

    SELECT idbarang INTO v_kunci
      FROM barang
     WHERE idbarang = NEW.idbarang
       FOR UPDATE;

    SELECT stock INTO v_saldo
      FROM kartu_stok
     WHERE idbarang = NEW.idbarang
     ORDER BY idkartu_stok DESC
     LIMIT 1;

    INSERT INTO kartu_stok (jenis_transaksi, masuk, keluar, stock, created_at, idtransaksi, idbarang)
    VALUES ('K', 0, NEW.jumlah, COALESCE(v_saldo, 0) - NEW.jumlah, NOW(), NEW.penjualan_idpenjualan, NEW.idbarang);
END$$

DELIMITER ;

-- Daftar objek setelah pemasangan: harus 3 function, 2 procedure, 2 trigger.
SELECT ROUTINE_TYPE, ROUTINE_NAME
  FROM information_schema.ROUTINES
 WHERE ROUTINE_SCHEMA = 'projek_pbd_094'
 ORDER BY ROUTINE_TYPE, ROUTINE_NAME;

SELECT TRIGGER_NAME, EVENT_MANIPULATION, EVENT_OBJECT_TABLE
  FROM information_schema.TRIGGERS
 WHERE TRIGGER_SCHEMA = 'projek_pbd_094'
 ORDER BY TRIGGER_NAME;
