<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AuthAccessTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        // Aplikasi memakai tabel, view, dan rutin MySQL; sqlite tidak cocok.
        if (DB::connection()->getDriverName() !== 'mysql') {
            $this->markTestSkipped('Butuh koneksi MySQL projek_pbd_094.');
        }
    }

    private function user(string $username): User
    {
        return User::query()->where('username', $username)->firstOrFail();
    }

    public function test_tamu_diarahkan_ke_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/penjualan')->assertRedirect('/login');
        $this->get('/login')->assertOk()->assertSee('Masuk');
    }

    public function test_password_salah_ditolak(): void
    {
        $this->post('/login', ['username' => 'manajer', 'password' => 'salah'])
            ->assertSessionHasErrors('username');

        $this->assertGuest();
    }

    public function test_setiap_role_bisa_login_dan_logout(): void
    {
        $akun = [
            'manajer' => 'manajer123',
            'admin01' => 'admin123',
            'kasir01' => 'kasir123',
            'staffgudang01' => 'gudang123',
            'staffpembelian01' => 'pembelian123',
        ];

        foreach ($akun as $username => $sandi) {
            $this->post('/login', ['username' => $username, 'password' => $sandi])
                ->assertRedirect('/dashboard');

            $this->assertAuthenticatedAs($this->user($username));

            $this->post('/logout')->assertRedirect('/login');
            $this->assertGuest();
        }
    }

    /** @return array<string, array{0: string, 1: array<int, string>, 2: array<int, string>}> */
    public static function matriks(): array
    {
        return [
            'manajer' => ['manajer', [
                '/dashboard', '/role', '/user', '/satuan', '/vendor', '/margin_penjualan',
                '/barang', '/pengadaan', '/penerimaan', '/penjualan', '/retur', '/kartu_stok', '/laporan',
            ], []],
            'admin' => ['admin01', [
                '/dashboard', '/role', '/user', '/satuan', '/vendor', '/margin_penjualan',
                '/barang', '/kartu_stok', '/laporan',
            ], ['/pengadaan', '/penerimaan', '/penjualan', '/retur']],
            'kasir' => ['kasir01', [
                '/dashboard', '/penjualan', '/penjualan/create',
            ], ['/barang', '/pengadaan', '/penerimaan', '/retur', '/user', '/role', '/laporan', '/margin_penjualan']],
            'staff gudang' => ['staffgudang01', [
                '/dashboard', '/barang', '/penerimaan', '/kartu_stok',
            ], ['/pengadaan', '/penjualan', '/retur', '/user', '/margin_penjualan']],
            'staff pembelian' => ['staffpembelian01', [
                '/dashboard', '/vendor', '/barang', '/pengadaan', '/kartu_stok', '/laporan',
            ], ['/penerimaan', '/penjualan', '/retur', '/user']],
        ];
    }

    #[DataProvider('matriks')]
    public function test_matriks_akses(string $username, array $boleh, array $dilarang): void
    {
        $this->actingAs($this->user($username));

        foreach ($boleh as $path) {
            $this->get($path)->assertOk();
        }

        foreach ($dilarang as $path) {
            $this->get($path)->assertForbidden()->assertSee('Akses ditolak');
        }
    }

    public function test_menu_dan_topbar_mengikuti_role(): void
    {
        $kasir = $this->actingAs($this->user('kasir01'))->get('/dashboard');
        $kasir->assertOk()->assertSee('kasir01')->assertSee('Kasir');
        $kasir->assertSee('Penjualan')->assertDontSee('Retur')->assertDontSee('Margin Penjualan');
        $kasir->assertSee('Ringkasan penjualan');

        $gudang = $this->actingAs($this->user('staffgudang01'))->get('/dashboard');
        $gudang->assertSee('Penerimaan')->assertSee('Staff Gudang');
        $gudang->assertDontSee('Pengadaan')->assertDontSee('Penjualan');
        $gudang->assertDontSee('Ringkasan penjualan');
    }

    public function test_tautan_ke_modul_lain_disembunyikan(): void
    {
        $idPenerimaan = DB::table('penerimaan')->orderBy('idpenerimaan')->value('idpenerimaan');
        $idPengadaan = DB::table('pengadaan')->orderBy('idpengadaan')->value('idpengadaan');

        if ($idPenerimaan !== null) {
            $this->actingAs($this->user('staffgudang01'))
                ->get('/penerimaan/'.$idPenerimaan)
                ->assertOk()
                ->assertDontSee('Retur terkait')
                ->assertDontSee('/pengadaan/');
        }

        if ($idPengadaan !== null) {
            $this->actingAs($this->user('staffpembelian01'))
                ->get('/pengadaan/'.$idPengadaan)
                ->assertOk()
                ->assertDontSee('Penerimaan terkait')
                ->assertDontSee('/penerimaan/');
        }
    }

    public function test_laporan_disaring_sesuai_modul(): void
    {
        $admin = $this->actingAs($this->user('admin01'))->get('/laporan');
        $admin->assertOk()->assertSee('Laporan Stok')->assertDontSee('Laporan Penjualan');
        $this->get('/laporan/penjualan')->assertForbidden();

        $pembelian = $this->actingAs($this->user('staffpembelian01'))->get('/laporan');
        $pembelian->assertOk()->assertSee('Laporan Pengadaan')->assertDontSee('Laporan Penjualan');

        $manajer = $this->actingAs($this->user('manajer'))->get('/laporan');
        $manajer->assertOk()->assertSee('Laporan Penjualan')->assertSee('Laporan Stok');
        $this->get('/laporan/penjualan')->assertOk();
    }

    public function test_transaksi_mencatat_user_yang_login(): void
    {
        $admin = $this->user('admin01');
        $this->actingAs($admin);

        $this->post('/margin_penjualan', ['persen' => 7.5, 'status' => 0])
            ->assertRedirect('/margin_penjualan');

        $baris = DB::selectOne(
            'SELECT iduser FROM margin_penjualan ORDER BY idmargin_penjualan DESC LIMIT 1'
        );

        $this->assertSame((int) $admin->iduser, (int) $baris->iduser);
    }
}
