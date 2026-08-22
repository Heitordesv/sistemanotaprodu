<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Usuario;
use App\Models\Empresa;
use App\Helpers\Menu;
use Illuminate\Support\Str;

class UsuarioSeed extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

    private function validaPermissao()
    {
        $menu = new Menu();
        $temp = [];
        $menu = $menu->getMenu();
        foreach ($menu as $m) {
            foreach ($m['subs'] as $s) {
                array_push($temp, $s['rota']);
            }
        }
        return $temp;
    }

    public function run()
    {
        Empresa::create([
            'razao_social' => 'Slym',
            'nome_fantasia' => 'Slym',
            'rua' => 'Aldo ribas',
            'numero' => '190',
            'bairro' => 'Centro',
            'cidade_id' => 4081,
            'status' => 1,
            'email' => 'master@master.com',
            'telefone' => '00000000000',
            'cpf_cnpj' => '',
            'permissao' => '',
            'hash' => Str::random(30)
        ]);

        $todasPermissoes = $this->validaPermissao();

        Usuario::create([
            'nome' => 'Slym',
            'login' => 'slym',
            'senha' => '202cb962ac59075b964b07152d234b70',
            'adm' => 1,
            'ativo' => 1,
            'permissao' => json_encode($todasPermissoes),
            'empresa_id' => 1,
            'img' => '',
            'tema' => 1,
            'caixa_livre' => 0,
            'email' => '',
            'permite_desconto' => 1
        ]);

        $this->clearFolders();
    }

    private function clearFolders()
    {
        \Artisan::call('cache:clear');
        \Artisan::call('view:clear');
        \Artisan::call('route:clear');
        exec('echo "" > ' . storage_path('logs/laravel.log'));
        $folders = [
            'certificados',
            'barcode',
            // 'imgs_planos',
            'imgs_clientes',
            'imagens_loja_delivery',
            'imagens_categorias',
            // 'logos',
            'xml_nfe',
            'xml_nfce',
            'xml_cte',
            'xml_mdfe',
            'orcamento',
        ];
        foreach ($folders as $f) {
            $files = glob(public_path($f . '/*'));

            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
    }
}
