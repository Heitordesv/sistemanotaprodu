<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CarrosselDelivery;
use Stringable;
use Illuminate\Support\Str;
use App\Utils\UploadUtil;

class CarroselDeliveryController extends Controller
{
    protected $util;
    protected $empresa_id = null;
    public function __construct(UploadUtil $util)
    {
        $this->util = $util;

        if (!is_dir(public_path('carrossel_delivery'))) {
            mkdir(public_path('carrossel_delivery'), 0777, true);
        }
        $this->middleware(function ($request, $next) {
            $this->empresa_id = $request->empresa_id;
            $value = session('user_logged');
            if (!$value) {
                return redirect("/login");
            }
            return $next($request);
        });
    }

    public function index()
    {
        $data = CarrosselDelivery::where('empresa_id', $this->empresa_id)
            ->orderBy('status', 'desc')
            ->orderBy('valor_ordem', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
        return view('carrossel_delivery.create', compact('data'));
    }

    public function store(Request $request)
    {
        $file_name = '';
        try {
            if ($request->hasFile('image')) {
                $file_name = $this->util->uploadImage($request, '/carrossel_delivery');
            }
            $last = CarrosselDelivery::where('empresa_id', $this->empresa_id)
                ->where('status', 1)
                ->orderBy('valor_ordem', 'desc')
                ->first();
            CarrosselDelivery::create([
                'empresa_id' => $this->empresa_id,
                'path' => $file_name,
                'status' => 1,
                'valor_ordem' => $last != null ? $last->valor_ordem : -10
            ]);
            session()->flash('flash_sucesso', 'Imagem cadastrada!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado: ' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->back();
    }

    public function up($id)
    {
        $item = CarrosselDelivery::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        $prox = CarrosselDelivery::where('empresa_id', $this->empresa_id)
            ->where('valor_ordem', '>', $item->valor_ordem)
            ->where('status', $item->status)
            ->orderBy('valor_ordem', 'desc')
            ->first();
        $item->valor_ordem = $prox != null ? $prox->valor_ordem + 1 : $item->valor_ordem + 1;
        $item->save();
        return redirect()->back();
    }

    public function down($id)
    {
        $item = CarrosselDelivery::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        $prox = CarrosselDelivery::where('empresa_id', $this->empresa_id)
            ->where('valor_ordem', '<', $item->valor_ordem)
            ->where('status', $item->status)
            ->orderBy('valor_ordem', 'desc')
            ->first();
        $item->valor_ordem = $prox != null ? $prox->valor_ordem - 1 : $item->valor_ordem - 1;
        $item->save();
        return redirect()->back();
    }

    public function delete($id)
    {
        $item = CarrosselDelivery::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        if ($item) {
            try {
                if (file_exists(public_path('carrossel_delivery/') . $item->path)) {
                    unlink(public_path('carrossel_delivery/') . $item->path);
                }
                $item->delete();
                session()->flash('flash_sucesso', 'imagem removida!');
            } catch (\Exception $e) {
                session()->flash('flash_erro', 'Algo deu errado: ' . $e->getMessage());
                __saveLogError($e, request()->empresa_id);
            }
            return redirect()->back();
        } else {
            return redirect('/403');
        }
    }

    public function alteraStatus($id)
    {
        $item = CarrosselDelivery::findOrFail($id);
        $item->status = !$item->status;
        $item->save();
    }
}
