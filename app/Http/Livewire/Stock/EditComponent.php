<?php

namespace App\Http\Livewire\Stock;

use App\Models\Almacen;
use App\Models\Productos;
use Illuminate\Support\Facades\Auth;
use App\Models\Stock;
use App\Models\ProductosCategories;
use App\Models\StockEntrante;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;
use Livewire\WithFileUploads;
use Carbon\Carbon;
use App\Models\RoturaStock;

class EditComponent extends Component
{
    use LivewireAlert;
    use WithFileUploads;

    public $identificador;
    public $observaciones;
    public $qr_id;
    public $estado;
    public $producto_seleccionado;
    public $almacenes;
    public $almacen_id;
    public $productos;
    public $fecha;
    public $cantidad;
    public $stockentrante;
    public $stock;
    public $almacenActual;


    public $roturaStockItem;
    public $addStockItem;
    public $deleteStockItem;

    public function mount()
    {
        $this->stockentrante = StockEntrante::find( $this->identificador);
        $this->stock = Stock::find(  $this->stockentrante->stock_id);
        $this->estado = $this->stock->estado;
        $this->cantidad = $this->stockentrante->cantidad;
        $this->qr_id = $this->stock->qr_id;
        $this->fecha = $this->stock->fecha;
        $this->observaciones = $this->stock->observaciones;
        $this->productos = Productos::all();
        $this->almacenes = Almacen::all();
        $user = Auth::user();
        $this->almacen_id = $this->stock->almacen_id;
        $this->almacenActual = $this->almacenes->find($this->stock->almacen_id);


    }

    public function render()
    {
        return view('livewire.stock.edit-component');
    }

    // Al hacer update en el formulario
    public function update()
    {
        if( 0 > $this->cantidad ){
            $this->alert('error', '¡No se puede asignar una cantidad negativa!', [
                'position' => 'center',
                'timer' => 3000,
                'toast' => false,
            ]);
            return;
         }

         if ($this->cantidad == 0){
            $this->stock->update([
                'estado' => 2,
            ]);
         }


        $productUpdate = $this->stockentrante->update([
            'cantidad' => $this->cantidad,
        ]);

        $this->stock->update([
            'estado' =>  $this->estado,
            'observaciones' => $this->observaciones,
        ]);

        if($this->roturaStockItem > 0){
            $roturaStock = new RoturaStock();
            $roturaStock->stock_id = $this->stock->id;
            $roturaStock->cantidad = $this->roturaStockItem;
            $roturaStock->fecha = Carbon::now();
            //$roturaStock->observaciones = 'Rotura de stock';
            $roturaStock->almacen_id = $this->almacen_id;
            $roturaStock->user_id = Auth::user()->id;
            $roturaStock->save();
        }


        if ($productUpdate) {
            $this->alert('success', '¡Stock actualizado correctamente!', [
                'position' => 'center',
                'timer' => 3000,
                'toast' => false,
                'showConfirmButton' => true,
                'onConfirmed' => 'confirmed',
                'confirmButtonText' => 'ok',
                'timerProgressBar' => true,
            ]);
        } else {
            $this->alert('error', '¡No se ha podido guardar la información del Stock!', [
                'position' => 'center',
                'timer' => 3000,
                'toast' => false,
            ]);
        }

    }

    public function  addStock(){
        $this->addStockItem = abs($this->addStockItem);
        $this->cantidad = $this->cantidad + $this->addStockItem;

    }

    public function  deleteStock(){

        $this->deleteStockItem = abs($this->deleteStockItem);

            
        $this->cantidad = $this->cantidad - $this->deleteStockItem;
        //si es un numero negativo se convierte en positivo, es decir, se le quita el signo negativo
        
    }

    public function  roturaStock(){

        $this->roturaStockItem = abs($this->roturaStockItem);

        $this->cantidad = $this->cantidad - $this->roturaStockItem;

        $this->alert('warning', '¿Seguro que desea registrar la rotura de stock?', [
            'position' => 'center',
            'timer' => 3000,
            'toast' => false,
            'showConfirmButton' => true,
            'onConfirmed' => 'update',
            'confirmButtonText' => 'Sí',
            'showDenyButton' => true,
            'denyButtonText' => 'No',
            'timerProgressBar' => true,
        ])
        ;
       
        

    }

    // Elimina el producto
    public function destroy()
    {

        $this->alert('warning', '¿Seguro que desea borrar el producto? No hay vuelta atrás', [
            'position' => 'center',
            'timer' => 3000,
            'toast' => false,
            'showConfirmButton' => true,
            'onConfirmed' => 'confirmDelete',
            'confirmButtonText' => 'Sí',
            'showDenyButton' => true,
            'denyButtonText' => 'No',
            'timerProgressBar' => true,
        ]);
    }

    // Función para cuando se llama a la alerta
    public function getListeners()
    {
        return [
            'confirmed',
            'confirmDelete',
            'update'
        ];
    }

    // Función para cuando se llama a la alerta
    public function confirmed()
    {
        // Do something
        return redirect()->route('stock.index');
    }
    // Función para cuando se llama a la alerta
    public function confirmDelete()
    {
        $product = Productos::find($this->identificador);
        $product->delete();
        return redirect()->route('stock.index');
    }
    public function getProductoNombre()
    {
        $producto = Productos::find($this->producto_seleccionado);
        if ($producto != null && $producto->nombre != null) {
            return $producto->nombre;
        }
    }

    public function getProductoImagen()
    {
        $producto = Productos::find($this->producto_seleccionado);
        if ($producto != null && $producto->foto_ruta != null) {
            return $producto->foto_ruta;
        }
    }

    public function getNombreTabla($id)
    {
        $nombre_producto = $this->productos->where('id', $id)->first()->nombre;
        return $nombre_producto;
    }



}
