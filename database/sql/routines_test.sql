-- =====================================================
-- Daily Bun - Uji 3 Function, 2 Stored Procedure, dan 2 Trigger
--
-- JALANKAN HANYA DI DATABASE UJI, bukan database asli:
--   1. Salin dml_ddl.sql, views.sql, routines.sql, ganti nama database
--      'projek_pbd_094' menjadi 'projek_pbd_094_uji' (cari-ganti), lalu jalankan.
--   2. Jalankan file ini. Seluruh data uji dibuat di dalam transaksi dan
--      di-ROLLBACK di akhir, jadi tidak ada data tersisa
--      (nomor AUTO_INCREMENT tetap maju; itu normal di MySQL).
--
-- Kolom 'hasil' bernilai LULUS / GAGAL.
-- Validasi data dilakukan aplikasi, bukan trigger/SP, sehingga tidak ada
-- uji SQLSTATE/penolakan di file ini.
-- =====================================================
USE projek_pbd_094_uji;

SET @iduser   = (SELECT iduser FROM `user` ORDER BY iduser LIMIT 1);
SET @idvendor = (SELECT idvendor FROM vendor ORDER BY idvendor LIMIT 1);
SET @b1       = (SELECT idbarang FROM barang ORDER BY idbarang LIMIT 1);
SET @b2       = (SELECT idbarang FROM barang ORDER BY idbarang LIMIT 1 OFFSET 1);

SET @kartu_awal  = (SELECT COUNT(*) FROM kartu_stok);
SET @detail_awal = (SELECT COUNT(*) FROM detail_penerimaan);
SET @jual_awal   = (SELECT COUNT(*) FROM detail_penjualan);
SET @margin_awal = (SELECT COUNT(*) FROM margin_penjualan);
SET @awal1       = COALESCE((SELECT stock FROM kartu_stok WHERE idbarang = @b1
                              ORDER BY idkartu_stok DESC LIMIT 1), 0);
SET @awal2       = COALESCE((SELECT stock FROM kartu_stok WHERE idbarang = @b2
                              ORDER BY idkartu_stok DESC LIMIT 1), 0);

START TRANSACTION;

-- ---------- A. FUNCTION ----------
SELECT 'A1. subtotal 10 x 10000' AS uji, fn_hitung_subtotal(10000, 10) AS nilai,
       100000 AS diharapkan,
       CASE WHEN fn_hitung_subtotal(10000, 10) = 100000 THEN 'LULUS' ELSE 'GAGAL' END AS hasil
UNION ALL
SELECT 'A2. subtotal 12500 x 3', fn_hitung_subtotal(12500, 3), 37500,
       CASE WHEN fn_hitung_subtotal(12500, 3) = 37500 THEN 'LULUS' ELSE 'GAGAL' END
UNION ALL
SELECT 'A3. jumlah 0 -> 0', fn_hitung_subtotal(5000, 0), 0,
       CASE WHEN fn_hitung_subtotal(5000, 0) = 0 THEN 'LULUS' ELSE 'GAGAL' END
UNION ALL
SELECT 'A4. harga NULL -> 0', fn_hitung_subtotal(NULL, 5), 0,
       CASE WHEN fn_hitung_subtotal(NULL, 5) = 0 THEN 'LULUS' ELSE 'GAGAL' END
UNION ALL
SELECT 'A5. jumlah NULL -> 0', fn_hitung_subtotal(5000, NULL), 0,
       CASE WHEN fn_hitung_subtotal(5000, NULL) = 0 THEN 'LULUS' ELSE 'GAGAL' END
UNION ALL
SELECT 'A6. harga negatif -> 0', fn_hitung_subtotal(-1000, 5), 0,
       CASE WHEN fn_hitung_subtotal(-1000, 5) = 0 THEN 'LULUS' ELSE 'GAGAL' END
UNION ALL
SELECT 'A7. ppn 100000 x 11%', fn_hitung_ppn(100000, 11), 11000,
       CASE WHEN fn_hitung_ppn(100000, 11) = 11000 THEN 'LULUS' ELSE 'GAGAL' END
UNION ALL
SELECT 'A8. ppn pembulatan 1255 x 11%', fn_hitung_ppn(1255, 11), 138,
       CASE WHEN fn_hitung_ppn(1255, 11) = 138 THEN 'LULUS' ELSE 'GAGAL' END
UNION ALL
SELECT 'A9. ppn subtotal NULL -> 0', fn_hitung_ppn(NULL, 11), 0,
       CASE WHEN fn_hitung_ppn(NULL, 11) = 0 THEN 'LULUS' ELSE 'GAGAL' END
UNION ALL
SELECT 'A10. ppn persen negatif -> 0', fn_hitung_ppn(50000, -5), 0,
       CASE WHEN fn_hitung_ppn(50000, -5) = 0 THEN 'LULUS' ELSE 'GAGAL' END
UNION ALL
SELECT 'A11. fn_stok_barang id tidak ada -> 0', fn_stok_barang(0), 0,
       CASE WHEN fn_stok_barang(0) = 0 THEN 'LULUS' ELSE 'GAGAL' END;

-- ---------- B. TRIGGER PENERIMAAN & PENJUALAN ----------
INSERT INTO pengadaan (`timestamp`, user_iduser, status, vendor_idvendor, subtotal_nilai, ppn, total_nilai)
     VALUES (NOW(), @iduser, 'S', @idvendor, 0, 0, 0);
SET @pgd = LAST_INSERT_ID();

INSERT INTO penerimaan (created_at, status, idpengadaan, iduser)
     VALUES (NOW(), 'S', @pgd, @iduser);
SET @pen = LAST_INSERT_ID();

INSERT INTO detail_penerimaan (idpenerimaan, barang_idbarang, jumlah_terima, harga_satuan_terima, sub_total_terima)
     VALUES (@pen, @b1, 15, 5000, 75000), (@pen, @b2, 7, 10000, 70000);

SELECT 'B1. trigger penerimaan menulis kartu_stok M untuk tiap detail' AS uji,
       (SELECT COUNT(*) FROM detail_penerimaan WHERE idpenerimaan = @pen) AS jumlah_detail,
       (SELECT COUNT(*) FROM kartu_stok WHERE idtransaksi = @pen AND jenis_transaksi = 'M') AS jumlah_kartu,
       CASE WHEN (SELECT COUNT(*) FROM detail_penerimaan WHERE idpenerimaan = @pen) =
                 (SELECT COUNT(*) FROM kartu_stok WHERE idtransaksi = @pen AND jenis_transaksi = 'M')
            THEN 'LULUS' ELSE 'GAGAL' END AS hasil;

SELECT CONCAT('B2. saldo barang ', k.idbarang, ' = awal + ', k.masuk) AS uji,
       k.masuk, k.keluar, k.stock,
       CASE WHEN (k.idbarang = @b1 AND k.masuk = 15 AND k.keluar = 0 AND k.stock = @awal1 + 15) OR
                 (k.idbarang = @b2 AND k.masuk = 7  AND k.keluar = 0 AND k.stock = @awal2 + 7)
            THEN 'LULUS' ELSE 'GAGAL' END AS hasil
  FROM kartu_stok k
 WHERE k.idtransaksi = @pen AND k.jenis_transaksi = 'M'
 ORDER BY k.idbarang;

SELECT 'B3. fn_stok_barang barang 1 = awal + 15' AS uji,
       fn_stok_barang(@b1) AS stok_fn,
       @awal1 + 15 AS diharapkan,
       CASE WHEN fn_stok_barang(@b1) = @awal1 + 15 THEN 'LULUS' ELSE 'GAGAL' END AS hasil;

-- Uji trigger penjualan setelah stok masuk
SET @idmargin = (SELECT idmargin_penjualan FROM margin_penjualan ORDER BY idmargin_penjualan LIMIT 1);
INSERT INTO penjualan (created_at, subtotal_nilai, ppn, total_nilai, iduser, idmargin_penjualan)
     VALUES (NOW(), 0, 0, 0, @iduser, @idmargin);
SET @pjl = LAST_INSERT_ID();

INSERT INTO detail_penjualan (harga_satuan, jumlah, subtotal, penjualan_idpenjualan, idbarang)
     VALUES (12000, 4, 48000, @pjl, @b1);

SELECT 'B4. trigger penjualan menulis kartu_stok K & kurangi stok' AS uji,
       (SELECT keluar FROM kartu_stok WHERE idtransaksi = @pjl AND jenis_transaksi = 'K' ORDER BY idkartu_stok DESC LIMIT 1) AS keluar,
       fn_stok_barang(@b1) AS stok_terbaru,
       @awal1 + 11 AS diharapkan,
       CASE WHEN (SELECT keluar FROM kartu_stok WHERE idtransaksi = @pjl AND jenis_transaksi = 'K' ORDER BY idkartu_stok DESC LIMIT 1) = 4
                 AND fn_stok_barang(@b1) = @awal1 + 11
            THEN 'LULUS' ELSE 'GAGAL' END AS hasil;

SELECT CONCAT('B5. konsistensi saldo barang ', t.idbarang) AS uji, t.saldo_terakhir, t.sum_mutasi,
       CASE WHEN t.saldo_terakhir = t.sum_mutasi THEN 'LULUS' ELSE 'GAGAL' END AS hasil
  FROM (SELECT b.idbarang,
               fn_stok_barang(b.idbarang) AS saldo_terakhir,
               COALESCE((SELECT SUM(masuk - keluar) FROM kartu_stok WHERE idbarang = b.idbarang), 0) AS sum_mutasi
          FROM barang b WHERE b.idbarang IN (@b1, @b2)) t;

-- ---------- C. STORED PROCEDURE ----------
-- Dua margin uji: A aktif, B nonaktif.
INSERT INTO margin_penjualan (created_at, persen, status, iduser, updated_at)
     VALUES (NOW(), 10, 1, @iduser, NOW()),
            (NOW(), 20, 0, @iduser, NOW());
SET @m1 = LAST_INSERT_ID();
SET @m2 = @m1 + 1;

-- C1: aktifkan B -> B aktif, A nonaktif, hanya satu margin aktif.
CALL sp_aktifkan_margin_penjualan(@m2);

SELECT 'C1. aktifkan margin B' AS uji,
       (SELECT status FROM margin_penjualan WHERE idmargin_penjualan = @m1) AS status_a,
       (SELECT status FROM margin_penjualan WHERE idmargin_penjualan = @m2) AS status_b,
       (SELECT COUNT(*) FROM margin_penjualan WHERE status = 1) AS jumlah_aktif,
       CASE WHEN (SELECT status FROM margin_penjualan WHERE idmargin_penjualan = @m1) = 0
                 AND (SELECT status FROM margin_penjualan WHERE idmargin_penjualan = @m2) = 1
                 AND (SELECT COUNT(*) FROM margin_penjualan WHERE status = 1) = 1
            THEN 'LULUS' ELSE 'GAGAL' END AS hasil;

-- C2: aktifkan A lagi -> A aktif, B nonaktif.
CALL sp_aktifkan_margin_penjualan(@m1);

SELECT 'C2. aktifkan margin A' AS uji,
       (SELECT status FROM margin_penjualan WHERE idmargin_penjualan = @m1) AS status_a,
       (SELECT status FROM margin_penjualan WHERE idmargin_penjualan = @m2) AS status_b,
       (SELECT COUNT(*) FROM margin_penjualan WHERE status = 1) AS jumlah_aktif,
       CASE WHEN (SELECT status FROM margin_penjualan WHERE idmargin_penjualan = @m1) = 1
                 AND (SELECT status FROM margin_penjualan WHERE idmargin_penjualan = @m2) = 0
                 AND (SELECT COUNT(*) FROM margin_penjualan WHERE status = 1) = 1
            THEN 'LULUS' ELSE 'GAGAL' END AS hasil;

-- C3: id yang tidak ada -> tidak ada margin aktif.
CALL sp_aktifkan_margin_penjualan(0);

SELECT 'C3. id tidak ada -> semua nonaktif' AS uji,
       (SELECT COUNT(*) FROM margin_penjualan WHERE status = 1) AS jumlah_aktif,
       CASE WHEN (SELECT COUNT(*) FROM margin_penjualan WHERE status = 1) = 0
            THEN 'LULUS' ELSE 'GAGAL' END AS hasil;

-- C4: sp_ubah_status_barang toggle 1 -> 0 -> 1
SET @st_awal = (SELECT status FROM barang WHERE idbarang = @b1);
CALL sp_ubah_status_barang(@b1);
SET @st_1 = (SELECT status FROM barang WHERE idbarang = @b1);
CALL sp_ubah_status_barang(@b1);
SET @st_2 = (SELECT status FROM barang WHERE idbarang = @b1);

SELECT 'C4. sp_ubah_status_barang toggle bolak-balik' AS uji,
       @st_awal AS awal, @st_1 AS setelah_1, @st_2 AS setelah_2,
       CASE WHEN @st_1 <> @st_awal AND @st_2 = @st_awal THEN 'LULUS' ELSE 'GAGAL' END AS hasil;

ROLLBACK;

-- ---------- D. ROLLBACK ----------
SELECT 'D. setelah ROLLBACK: tidak ada data uji tersisa' AS uji,
       (SELECT COUNT(*) FROM kartu_stok)        AS kartu_stok,
       (SELECT COUNT(*) FROM detail_penerimaan) AS detail_penerimaan,
       (SELECT COUNT(*) FROM detail_penjualan)  AS detail_penjualan,
       (SELECT COUNT(*) FROM margin_penjualan)  AS margin_penjualan,
       CASE WHEN (SELECT COUNT(*) FROM kartu_stok) = @kartu_awal
                 AND (SELECT COUNT(*) FROM detail_penerimaan) = @detail_awal
                 AND (SELECT COUNT(*) FROM detail_penjualan) = @jual_awal
                 AND (SELECT COUNT(*) FROM margin_penjualan) = @margin_awal
            THEN 'LULUS' ELSE 'GAGAL' END AS hasil;
