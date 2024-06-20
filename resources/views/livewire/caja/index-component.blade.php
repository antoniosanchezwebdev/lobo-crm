<div class="container-fluid">
    <div class="page-title-box">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h4 class="page-title">CAJA</h4>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-right">
                    <li class="breadcrumb-item"><a href="javascript:void(0);">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="javascript:void(0);">Caja</a></li>
                    <li class="breadcrumb-item active">Ver movimientos</li>
                </ol>
            </div>
        </div> <!-- end row -->
    </div>
    <!-- end page-title -->


    <div class="row" style="align-items: start !important">
        
        <div class="col-md-12">
            <div class="card m-b-30">
                <div class="table-responsive card-body">
                    <h4 class="mt-0 header-title" wire:key='rand()'>Ver movimientos de caja</h4>
                    
                    <div class="row">
                        <div class="col-12">
                            <div class="col-md-12">
                                <div class="card m-b-30">
                                    <div class="d-flex flex-wrap">
                                        <div class="col-md-4">
                                            <h5>Elige un mes</h5>
                                            <div class="row">
                                                <div class="col-12">
                                                    <input type="month" class="form-control" wire:model="mes" wire:change="cambioMes">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <h5>Acciones</h5>
                                            <div class="d-flex flex-wrap">
                                                <div class="col-12">
                                                    <button class="w-100 btn btn-success mb-2" wire:click="Ingreso">Ingreso</button>
                                                </div>
                                                <div class="col-12">
                                                    <button class="w-100 btn btn-danger mb-2" wire:click="Gasto">Gasto</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <div  >
                                <label for="example-text-input" class="col-form-label">Tipo de movimiento</label>
                                <select class="form-control" id="select2-producto" wire:model="filtro">
                                    <option value="Todos">Todos</option>
                                    <option value="Ingreso">Ingreso</option>
                                    <option value="Gasto">Gasto</option>
                                </select>
                            </div>
                            <div >
                                <div  >
                                    <label for="example-text-input" class="col-form-label">Estado</label>
                                    <select class="form-control" wire:model="filtroEstado">
                                        <option value="Todos">Todos</option>
                                        <option value="Pendiente">Pendientes</option>
                                        <option value="Pagado">Pagado</option>
                                    </select>
                                </div>
                            </div>
                            <div >
                                <div  >
                                    <label for="example-text-input" class="col-form-label">Delegacion</label>
                                    <select class="form-control" id="select2-producto" wire:model="delegacion">
                                        <option value="Todos">Todos</option>
                                        @foreach ($delegaciones as $delegacion)
                                            <option value="{{ $delegacion->id }}">{{ $delegacion->nombre }}</option>
                                        @endforeach  
                                    </select>
                                </div>
                            </div>
                            <div >
                                <div  >
                                    <label for="example-text-input" class="col-form-label">Fecha del Pago</label>
                                    <input type="date" class="form-control" wire:model="fechaPago">
                                </div>
                            </div>
                            <div>
                                <div  >
                                    <label for="example-text-input" class="col-form-label">Fecha vencimiento</label>
                                    <input type="date" class="form-control" wire:model="fechaVencimiento">
                                </div>
                            </div>
                            <div >
                                <div  >
                                    <label for="example-text-input" class="col-form-label">Fecha</label>
                                    <input type="date" class="form-control" wire:model="fecha">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-2">
                        <div class="col-12">
                            <button wire:click="descargarTodosDocumentos" class="btn btn-primary">Descargar todos los documentos</button>
                        </div>
                    </div>


                    @if (count($caja) > 0)


                        <div class="table-responsive" x-data="{}" x-init="$nextTick(() => {
                            $('#tablaingresosgastos').DataTable({
                                responsive: true,
                                fixedHeader: {
                                    header: true,
                                    footer: true,
                                },
                                searching: false,
                                ordering: false,
                                paging: false,
                                info: false,
                                
                                                });
                                            })"
                                            wire:key='{{ rand() }}'>
                            <table id="tablaingresosgastos" class="table-sm table-striped table-bordered mt-1"
                                style="border-collapse: collapse; border-spacing: 0; width: 100%;"  wire:key='{{ rand() }}'>
                                <thead>
                                    <tr>
                                        <th>Ingresos</th>
                                        <th>Gastos</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>{{ $ingresos }}€</td>
                                        <td>{{ $gastos }}€</td>
                                        <td>{{ $ingresos - $gastos }}€</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="table-responsive" x-data="{}" x-init="$nextTick(() => {
                            $('#tablacaja').DataTable({
                                responsive: true,
                                fixedHeader: {
                                    header: true,
                                    footer: true,
                                },
                                searching: false,
                                ordering: false,
                                paging: false,
                                info: false,
                                dom: 'Bfrtip', // Este elemento define dónde se colocan los botones
                                buttons: [
                                                    {
                                                        extend: 'excelHtml5',
                                                        text: 'Exportar a Excel',
                                                        titleAttr: 'Excel',
                                                        className: 'btn-secondary px-3 py-1 mb-2'
                                                    },
                                                    {
                                                        extend: 'pdfHtml5',
                                                        text: 'Exportar a PDF',
                                                        titleAttr: 'PDF',
                                                        className: 'btn-secondary px-3 py-1 mb-2'
                                                    },
                                                     
                                                    {
                                                        extend: 'colvis',
                                                        text: 'Columnas',
                                                        titleAttr: 'Columnas',
                                                        className: 'btn-secondary px-3 py-1 mb-2'
                                                    },
                                                    ]
                                                });
                                            })"
                                            wire:key='{{ rand() }}'>
                            <table id="tablacaja" class="table-sm table-striped table-bordered mt-2"
                                style="border-collapse: collapse; border-spacing: 0; width: 100%;"  wire:key='{{ rand() }}'>
                                <thead>
                                    {{-- <tr>
                                        <th colspan="9">Saldo inicial</th>
                                        <th colspan="3">{{$saldo_inicial}}€</th>
                                    </tr> --}}
                                    <tr>
                                        <th scope="col">Fecha</th>
                                        <th scope="col">Nº Interno</th>
                                        <th scope="col">Nº Factura</th>
                                        <th scope="col">Estado</th>
                                        <th scope="col">Concepto</th>
                                        <th scope="col">Asociado</th>
                                        <th scope="col">Desglose</th>
                                        <!--<th scope="col">Estado</th> -->
                                        <th scope="col">Importe</th>
                                        <th>Compensado</th>
                                        <th scope="col">% Iva</th>
                                        <th scope="col">Retencion</th>
                                        <th scope="col">Descuento</th>
                                        <th scope="col">(+)</th>
                                        <th scope="col">(-)</th>
                                        <th>Pendiente</th>
                                        
                                        <th scope="col">Saldo</th>


                                        <th scope="col">Ver</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($caja as $tipoIndex => $tipo)
                                        <tr>
                                            <td>{{ $tipo->fecha }}</td>
                                            <td>{{ $tipo->nInterno }}</td>
                                            <td>{{ $tipo->nFactura }}</td>
                                            <td >
                                                <span @if($tipo->estado == "Pendiente") class="badge badge-warning" @elseif($tipo->estado == "Pagado") class="badge badge-success"  @endif>
                                                {{ $tipo->estado }}
                                                </span>
                                            </td>
                                            <td>{{ $tipo->descripcion }}</td>
                                            @if (isset($tipo->pedido_id))
                                                <td>{{ $this->getFactura($tipo->pedido_id) }}</td>
                                            @elseif($tipo->poveedor_id)
                                                <td>{{ $this->proveedorNombre($tipo->poveedor_id )}}</td>
                                            @else
                                                <td></td>
                                            @endif
                                            <td>{{$tipo->tipo_movimiento}}</td> 
                                            <!-- <td>
                                                @if ($tipo->tipo_movimiento == 'Gasto')
                                                    @switch($tipo->estado)
                                                        @case('Pendiente')
                                                        <span class="badge badge-warning">{{ $tipo->estado }}</span>
                                                            @break
                                                        @case("Pagado")
                                                        <span class="badge badge-success">{{ $tipo->estado }}</span>
                                                            @break
                                                        @case('Vencido')
                                                        <span class="badge badge-danger">{{ $tipo->estado }}</span>
                                                            @break
                                                        @default
                                                        <span class="badge badge-info">{{ $tipo->estado }}</span>
                                                    @endswitch
                                                @endif
                                            </td> -->
                                            <td> @if (isset($tipo->pedido_id)){{ $tipo->importe  + $this->getCompensacion($tipo->pedido_id, $tipo->tipo_movimiento) }}€ @else {{ $tipo->importe }}€ @endif</td>
                                            <td>
                                                @if(isset($tipo->pedido_id)){{ $this->getCompensacion($tipo->pedido_id, $tipo->tipo_movimiento) }}
                                                @else
                                                 {{ $this->getCompensacion($tipo->id, $tipo->tipo_movimiento) }}
                                                @endif
                                            </td>
                                            @if($tipo->tipo_movimiento == 'Gasto')
                                                <td>{{ floatval($tipo->iva) }}%</td>
                                                <td>{{ floatval($tipo->retencion) }}%</td>
                                                <td>{{ floatval($tipo->descuento) }}%</td>
                                            @else
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                            @endif
                                            <td>
                                                @if ($tipo->tipo_movimiento == 'Ingreso')
                                                    @php($ingresos += $tipo->importe)
                                                    {{ $tipo->importe }}€
                                                @endif
                                            </td>
                                            <td>
                                                    @if ($tipo->tipo_movimiento == 'Gasto')
                                                        @if($tipo->pagado != null)
                                                            {{ floatval($tipo->pagado) - $this->getCompensacion($tipo->id,$tipo->tipo_movimiento ) }}€
                                                        @else
                                                            @if($tipo->estado == 'Pendiente')
                                                                0€
                                                            @else

                                                                {{ floatval($tipo->total)- $this->getCompensacion($tipo->id,$tipo->tipo_movimiento ) }}€
                                                            @endif
                                                        @endif
                                                        
                                                    @endif
                                            </td>
                                            <td>
                                                @if($tipo->estado == 'Pendiente')
                                                   
                                                        @if ($tipo->tipo_movimiento == 'Gasto')
                                                                <span  class="badge badge-warning" >
                                                                    @if($tipo->pendiente == null)
                                                                        {{ floatval($tipo->total) }}€
                                                                    @else
                                                                        {{ floatval($tipo->pendiente) }}€
                                                                    @endif
                                                                </span>
                                                        @endif
                                                    
                                                @endif
                                            </td>
                                            <td>{{ $this->calcular_saldo($tipoIndex, $tipo->id) }}€</td>


                                            <td> <a href="caja-edit/{{ $tipo->id }}"
                                                    class="btn btn-primary btn-sm"><i class="fa fa-eye"></i></a></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
    </div>

    @section('scripts')

    <script>
        
        
        


    </script>


    <script src="../assets/js/jquery.slimscroll.js"></script>
<link href="https://cdn.datatables.net/v/bs5/jq-3.7.0/jszip-3.10.1/dt-2.0.3/b-3.0.1/b-colvis-3.0.1/b-html5-3.0.1/b-print-3.0.1/r-3.0.1/datatables.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/v/bs5/jq-3.7.0/jszip-3.10.1/dt-2.0.3/b-3.0.1/b-colvis-3.0.1/b-html5-3.0.1/b-print-3.0.1/r-3.0.1/datatables.min.js"></script>
<!-- Responsive examples -->
<script src="../assets/pages/datatables.init.js"></script>

    @endsection
