<?php

namespace App\Http\Livewire\Pedidos;

use App\Models\Productos;
use App\Models\Clients;
use App\Models\Pedido;
use App\Models\ProductoLote;
use App\Models\PedidosStatus;
use Illuminate\Support\Facades\Auth;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CreateComponent extends Component
{
    use LivewireAlert;
    public $cliente_id;
    public $nombre;
    public $precio = 0;
    public $precioEstimado = 0;
    public $precioSinDescuento;
    public $estado = 1;
    public $direccion_entrega;
    public $provincia_entrega;
    public $localidad_entrega;
    public $cod_postal_entrega;
    public $orden_entrega;
    public $fecha;
    public $observaciones;
    public $tipo_pedido_id = 1;
    public $productos_pedido = [];
    public $productos;
    public $descuento = 0;
    public $clientes;
    public $unidades_producto = 0;
    public $addProducto = 0;
    public $producto_seleccionado;
    public $unidades_pallet_producto;
    public $unidades_caja_producto;
    public $precio_crema;
    public $precio_vodka07l;
    public $precio_vodka175l;
    public $precio_vodka3l;


    public function mount()
    {

        $this->productos = Productos::all();
        $this->clientes = Clients::where('estado', 2)->get();
        $this->fecha = Carbon::now()->format('Y-m-d');
        $this->estado = 1;
        $this->cliente_id = null;
    }

    public function selectCliente()
    {
        $cliente = Clients::find($this->cliente_id);
        $this->localidad_entrega = $cliente->localidad;
        $this->provincia_entrega = $cliente->provincia;
        $this->direccion_entrega = $cliente->direccion;
        $this->cod_postal_entrega = $cliente->cod_postal;
        $this->precio_crema = $cliente->precio_crema;
        $this->precio_vodka07l = $cliente->precio_vodka07l;
        $this->precio_vodka175l = $cliente->precio_vodka175l;
        $this->precio_vodka3l = $cliente->precio_vodka3l;
    }
    protected $listeners = ['refreshComponent' => '$refresh'];

    public function render()
    {
        return view('livewire.pedidos.create-component');
    }
    // Al hacer submit en el formulario
    public function submit()
    {

        // Validación de datos
        $validatedData = $this->validate(
            [
                'cliente_id' => 'required',
                'nombre' => 'nullable',
                'precio' => 'required',
                'estado' => 'required',
                'fecha' => 'required',
                'tipo_pedido_id' => 'required',
                'observaciones' => 'nullable',
                'direccion_entrega' => 'nullable',
                'provincia_entrega' => 'nullable',
                'localidad_entrega' => 'nullable',
                'cod_postal_entrega' => 'nullable',
                'orden_entrega' => 'nullable',
                'descuento'=> 'nullable',
            ],
            // Mensajes de error
            [
                'precio.required' => 'El precio del pedido es obligatorio.',
                'cliente_id.required' => 'El cliente es obligatorio.',
                'estado.required' => 'El estado del pedido es obligatoria.',
                'fecha.required' => 'La fecha es obligatoria.',
            ]
        );

        // Guardar datos validados
        $pedidosSave = Pedido::create($validatedData);

        foreach ($this->productos_pedido as $productos) {
            DB::table('productos_pedido')->insert([
                'producto_lote_id' => $productos['producto_lote_id'],
                'pedido_id' => $pedidosSave->id,
                'unidades' => $productos['unidades'],
                'precio_ud' => $productos['precio_ud'],
                'precio_total' => $productos['precio_total']
            ]);
            $producto_stock = ProductoLote::find($productos['producto_lote_id']);
            $cantidad_actual = $producto_stock->cantidad_actual - $productos['unidades'];
            $producto_stock->update(['cantidad_actual' => $cantidad_actual]);
        }
        event(new \App\Events\LogEvent(Auth::user(), 3, $pedidosSave->id));

        // Alertas de guardado exitoso
        if ($pedidosSave) {
            $this->alert('success', '¡Pedido registrado correctamente!', [
                'position' => 'center',
                'timer' => 3000,
                'toast' => false,
                'showConfirmButton' => true,
                'onConfirmed' => 'confirmed',
                'confirmButtonText' => 'ok',
                'timerProgressBar' => true,
            ]);
        } else {
            $this->alert('error', '¡No se ha podido guardar la información del pedido!', [
                'position' => 'center',
                'timer' => 3000,
                'toast' => false,
            ]);
        }
    }

    // Función para cuando se llama a la alerta
    public function getListeners()
    {
        return [
            'confirmed',
            'submit',
            'alertaGuardar',
            'checkLote'
        ];
    }

    public function alertaGuardar()
    {
        $this->alert('warning', 'Asegúrese de que todos los datos son correctos antes de guardar.', [
            'position' => 'center',
            'toast' => false,
            'showConfirmButton' => true,
            'onConfirmed' => 'submit',
            'confirmButtonText' => 'Sí',
            'showDenyButton' => true,
            'denyButtonText' => 'No',
            'timerProgressBar' => true,
        ]);
    }

    public function updatePallet()
    {
        $producto = Productos::find($this->producto_seleccionado);
        $this->unidades_caja_producto = $this->unidades_pallet_producto * $producto->cajas_por_pallet;
        $this->unidades_producto = $this->unidades_caja_producto * $producto->unidades_por_caja;
    }
    public function updateCaja()
    {
        $producto = Productos::find($this->producto_seleccionado);
        $this->unidades_pallet_producto = floor($this->unidades_caja_producto / $producto->cajas_por_pallet);
        $this->unidades_producto = $this->unidades_caja_producto * $producto->unidades_por_caja;
    }

    public function deleteArticulo($id)
    {
        unset($this->productos_pedido[$id]);
        $this->productos_pedido = array_values($this->productos_pedido);
        $this->setPrecioEstimado();
        $this->emit('refreshComponent');
    }

    public function getNombreTabla($id)
    {
        $nombre_producto = $this->productos->where('id', $id)->first()->nombre;
        return $nombre_producto;
    }
    public function getUnidadesTabla($id)
    {
        $producto = Productos::find($this->productos_pedido[$id]['producto_lote_id']);
        $cajas = ($this->productos_pedido[$id]['unidades'] / $producto->unidades_por_caja);
        $pallets = floor($cajas / $producto->cajas_por_pallet);
        $cajas_sobrantes = $cajas % $producto->cajas_por_pallet;
        $unidades = '';
        if ($cajas_sobrantes > 0) {
            $unidades = $this->productos_pedido[$id]['unidades'] . ' unidades (' . $pallets . ' pallets, y ' . $cajas_sobrantes . ' cajas)';
        } else {
            $unidades = $this->productos_pedido[$id]['unidades'] . ' unidades (' . $pallets . ' pallets)';
        }
        return $unidades;
    }

    public function addProductos($id)
    {
        $producto = Productos::find($id);
        if (!$producto) {
            // Muestra una alerta al usuario indicando que el producto no se encontró
            $this->alert('error', 'Producto no encontrado.', [
                'position' => 'center',
                'timer' => 3000,
                'toast' => false,
                'showConfirmButton' => false,
                'timerProgressBar' => true,
            ]);
            return;
        }

        $precioUnitario = $this->obtenerPrecioPorTipo($producto->tipo_precio);
        $precioTotal = $precioUnitario * $this->unidades_producto;

        $producto_existe = false;
        foreach ($this->productos_pedido as $productoPedido) {
            if ($productoPedido['producto_lote_id'] == $id) {
                $producto_existe = true;
                break;
            }
        }

        if ($producto_existe) {
            $key = array_search($id, array_column($this->productos_pedido, 'producto_lote_id'));
            $this->productos_pedido[$key]['unidades'] += $this->unidades_producto;
            $this->productos_pedido[$key]['precio_ud'] = $precioUnitario;
            $this->productos_pedido[$key]['precio_total'] += $precioTotal;
        } else {
            $this->productos_pedido[] = [
                'producto_lote_id' => $id,
                'unidades' => $this->unidades_producto,
                'precio_ud' => $precioUnitario,
                'precio_total' => $precioTotal
            ];
        }

        $this->setPrecioEstimado();
        $this->emit('refreshComponent');
    }
    private function obtenerPrecioPorTipo($tipoPrecio)
    {
        switch ($tipoPrecio) {
            case 1:
                return $this->precio_crema;
            case 2:
                return $this->precio_vodka07l;
            case 3:
                return $this->precio_vodka175l;
            case 4:
                return $this->precio_vodka3l;
            default:
                return 0;
        }
    }


    public function setPrecioEstimado()
{
    $this->precioEstimado = 0;
    foreach ($this->productos_pedido as $producto) {
        $this->precioEstimado += $producto['precio_total'];
    }
    $this->precioSinDescuento = $this->precioEstimado;
    // Verificar si el descuento está activado
    if ($this->descuento) {
        // Calcular el 3% de descuento del precio total
        $descuento = $this->precioEstimado * 0.03;
        // Aplicar el descuento al precio total
        $this->precioEstimado -= $descuento;
    }

    // Asignar el precio final
   $this->precio = number_format($this->precioEstimado, 2, '.', '');
}

    public function getProductoNombre()
    {
        $producto = Productos::find($this->producto_seleccionado);
        if ($producto != null && $producto->nombre != null) {
            return $producto->nombre;
        }
    }
    public function getProductoPrecio()
    {
        $producto = Productos::find($this->producto_seleccionado);
        if ($producto != null && $producto->precio != null) {
            return $producto->precio . "€ (Sin IVA)";
        }
    }
    public function getProductoPrecioIVA()
    {
        $producto = Productos::find($this->producto_seleccionado);
        if ($producto != null && $producto->precio != null) {
            return ($producto->precio + ($producto->precio * ($producto->iva / 100))) . "€ (Con IVA)";
        }
    }

    public function getProductoImg()
    {
        $producto = Productos::find($this->producto_seleccionado);
        if ($producto != null) {
            return asset('storage/photos/' . $producto->foto_ruta);
        }

        $this->emit('refreshComponent');
    }

    // Función para cuando se llama a la alerta
    public function confirmed()
    {
        // Do something
        return redirect()->route('pedidos.index');
    }

    public function getEstadoNombre()
    {
        return PedidosStatus::firstWhere('id', $this->estado)->status;
    }
}
