<?php

namespace App\Http\Livewire\Caja;

use App\Models\Caja;
use App\Models\Clients;
use App\Models\Pedido;
use App\Models\Facturas;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Bancos;
use App\Models\FacturasCompensadas;
use Illuminate\Support\Facades\DB;
use App\Models\Productos;
use App\Models\StockEntrante;


class CreateIngresoComponent extends Component
{
    use LivewireAlert;

    public $tipo_movimiento = 'Ingreso';
    public $metodo_pago = "giro_bancario";
    public $importe;
    public $descripcion;
    public $pedido_id;
    public $fecha;
    public $clientes;
    public $pedido;
    public $facturas;
    public $bancos;
    public $banco; //banco_id
    public $bancoSeleccionado;
    public $compensacion;
    public $facturaSeleccionada;
    public $importeFactura;
    public $ingresos_factura = [];
    public $compensacion_factura = false;
    public $facturas_compensadas = [];
    public $importeFacturaCompensada;
    public $importeCompensado;



    public function mount()
    {

        $this->facturas = Facturas::where('estado', 'Pendiente')
        ->orWhere('estado', 'Parcial')
        ->get();
        $this->clientes = Clients::all();
        $this->bancos = Bancos::all();
    }
    public function render()
    {
        if($this->banco){
            $this->bancoSeleccionado = Bancos::find($this->banco);
        }


        return view('livewire.caja.create-ingreso-component');

    }


    public function onFacturaChange($id)
    {
        if(isset($id) && $id != null){
            $this->facturaSeleccionada = Facturas::find($id);
            $this->importeFactura = $this->facturaSeleccionada->total;
            $this->ingresos_factura = Caja::where('pedido_id', $id)->get();
            $this->facturas_compensadas = Facturas::where('factura_id', $id)->get();
            $total = 0;
            foreach ($this->facturas_compensadas as $factura) {
                $productosFactura = DB::table('productos_factura')->where('factura_id', $factura->id)->get();
                $productosdeFactura = [];
                foreach ($productosFactura as $productoPedido) {
                    $producto = Productos::find($productoPedido->producto_id);
                    $stockEntrante = StockEntrante::where('id', $productoPedido->stock_entrante_id)->first();
                
                    if ($stockEntrante) {
                        $lote = $stockEntrante->orden_numero;
                    } else {
                        $lote = "";
                    }
                    if ($producto) {
                        if (!isset($producto->peso_neto_unidad) || $producto->peso_neto_unidad <= 0) {
                            $peso = "Peso no definido";
                        } else {
                            $peso = ($producto->peso_neto_unidad * $productoPedido->unidades) / 1000;
                        }
                        $productosdeFactura[] = [
                            'nombre' => $producto->nombre,
                            'cantidad' => $productoPedido->cantidad,
                            'precio_ud' => $productoPedido->precio_ud,
                            'precio_total' =>  ($productoPedido->cantidad * $productoPedido->precio_ud),
                            'iva' => $producto->iva != 0 ?  (($productoPedido->cantidad * $productoPedido->precio_ud) * $producto->iva / 100) : (($productoPedido->cantidad * $productoPedido->precio_ud) * 21 / 100) ,
                            'lote_id' => $lote,
                            'peso_kg' =>  $peso,
                        ];
                    }
                }
                
                $base_imponible = 0;
                $iva_productos = 0;

                if ($factura->tipo == 2){
                    
                    foreach ($productosdeFactura as $producto) {
                        $base_imponible += $producto['precio_total'];
                        $iva_productos += $producto['iva'];
                    }
                    $total = $base_imponible + $iva_productos;

                }
           
            }


            //dd($this->ingresos_factura->sum('importe'));
            if(count($this->ingresos_factura) > 0){
                $this->importe = $this->importeFactura - $this->ingresos_factura->sum('importe');
                

            }else{
                $this->importe = $this->importeFactura;
            }
            
            if(count($this->facturas_compensadas) > 0){
                $this->importeFacturaCompensada = $this->importe - $total;
                $this->importeCompensado = $total;
                $this->compensacion_factura = true;
            }else{
                $this->compensacion_factura = false;
            }

        }
    }

    public function submit()
    { 
            // Validación de datos
            $validatedData = $this->validate(
                [
                    'tipo_movimiento' => 'required',
                    'metodo_pago' => 'required',
                    'importe' => 'required',
                    'descripcion' => 'required',
                    'pedido_id' => 'required',
                    'fecha' => 'required',
                    'banco' => 'nullable'


                ],
                // Mensajes de error
                [
                    'tipo_movimiento.required' => 'El tipo de movimiento es obligatorio.',
                    'metodo_pago.required' => 'El método de pago es obligatorio.',
                    'importe.required' => 'El importe es obligatorio.',
                    'descripcion.required' => 'La descripción es obligatoria.',
                    'pedido_id.required' => 'El pedido es obligatorio.',
                    'fecha.required' => 'La fecha es obligatoria.',
                ]
            );
        
        // Guardar datos validados
        $usuariosSave = Caja::create([
            'tipo_movimiento' => $this->tipo_movimiento,
            'metodo_pago' => $this->metodo_pago,
            'importe' =>  $this->compensacion_factura ? $this->importeFacturaCompensada :  $this->importe,
            'descripcion' => $this->descripcion,
            'pedido_id' => $this->pedido_id,
            'fecha' => $this->fecha,
            'banco' => $this->banco,
        ]);
        event(new \App\Events\LogEvent(Auth::user(), 52, $usuariosSave->id));


        $this->importeFactura = $this->facturaSeleccionada->total;
        $this->ingresos_factura = Caja::where('pedido_id', $this->facturaSeleccionada->id)->get();
        $importe = $this->importeFactura;
        if(count($this->ingresos_factura) > 0){
            $importe = $this->importeFactura - $this->ingresos_factura->sum('importe');

        }

        if($importe - $this->importeCompensado <= 0 ){
            $this->facturaSeleccionada->estado = 'Pagado';
            $this->facturaSeleccionada->save();
        }else{
            $this->facturaSeleccionada->estado = 'Parcial';
            $this->facturaSeleccionada->save();
        }


        // Alertas de guardado exitoso
        if ($usuariosSave) {
            $this->alert('success', '¡Movimiento registrado correctamente!', [
                'position' => 'center',
                'timer' => 3000,
                'toast' => false,
                'showConfirmButton' => true,
                'onConfirmed' => 'confirmed',
                'confirmButtonText' => 'ok',
                'timerProgressBar' => true,
            ]);
        } else {
            $this->alert('error', '¡No se ha podido guardar la información del movimiento!', [
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
            'onFacturaChange'
        ];
    }

    // Función para cuando se llama a la alerta
    public function confirmed()
    {
        // Do something
        return redirect()->route('caja.index');
    }

    public function getCliente($id)
    {
         return $this->clientes->firstWhere('id', $id)->nombre;
    }
}
