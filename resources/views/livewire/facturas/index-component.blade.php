<div class="container-fluid mx-auto">
    <style>
        @media (max-width: 768px) {
            #filtrosSelect {
                width: 100%;
                flex-wrap: wrap;
                justify-content: start !important;
                gap: 20px !important;
                margin-bottom: 10px;
            }

            .botones{
                width: 100%;
                margin: 10px;
                display: block;
            }
        }
    </style>
    <div class="page-title-box">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h4 class="page-title">TODAS LAS FACTURAS</span></h4>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-right">
                    <li class="breadcrumb-item"><a href="javascript:void(0);">Dashboard</a></li>
                    {{-- <li class="breadcrumb-item"><a href="javascript:void(0);">Contratos</a></li> --}}
                    <li class="breadcrumb-item active">Facturas</li>
                </ol>
            </div>
        </div> <!-- end row -->
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card m-b-30">
                <div class="card-body" id="table-container">
                    <h4 class="mt-0 header-title">Listado de todas las facturas</h4>
                    <p class="sub-title../plugins">Listado completo de todas nuestros facturas, para editar o ver la
                        informacion completa pulse el boton de Editar en la columna acciones.
                    </p>
                    @if(Auth::user()->role != 3)
                        <div class="col-12 mb-5">
                            <a href="facturas-create" class="btn btn-lg w-100 btn-primary">Crear factura</a>
                        </div>
                    @endif
                    <div class="d-flex gap-1 justify-content-end align-items-end flex-column flex-wrap" >
                        
                        <div class="d-flex gap-2 justify-content-end" id="filtrosSelect" >
                            <div class="filtro d-flex flex-column">
                                <label class=""  id="comerciales"  >
                                    Comerciales
                                    </label>
                                <select class="text-white bg-secondary rounded p-1" id="comercialesSelect"  wire:model="comercialSeleccionadoId">
                                    <option value="-1">Todos</option>
                                    @foreach ( $comerciales as $comercial )
                                        <option value='{{ $comercial->id }}'>{{ $comercial->name }}</option>
                                    @endforeach
                                    <!-- Agrega más ítems según las columnas de tu tabla -->
                                </select>                            
                            </div>
                            <div class="filtro d-flex flex-column" >
                                <label class=""  id="delegaciones"  >
                                Delegaciones
                                </label>
                                <select class="text-white bg-secondary rounded p-1" id="delegacionesSelect"  wire:model="delegacionSeleccionadaCOD">
                                    <option value='-1' >Todas</option>
                                    @foreach ( $delegaciones as $delegacion )
                                        <option value='{{  $delegacion->COD }}'>{{ $delegacion->nombre }}</option>
                                    @endforeach
                                    <!-- Agrega más ítems según las columnas de tu tabla -->
                                    </select>                            
                            </div>
                            <div class="filtro d-flex flex-column" >
                                <label class=""  id="clientes"  >
                                Clientes
                                </label>
                                <select class="text-white bg-secondary rounded p-1" id="clientesSelect"   wire:model="clienteSeleccionadoId">
                                    <option value='-1' >Todos</option>
                                    @foreach ( $clientes as $cliente )
                                        <option value='{{$cliente->id }}' >{{ $cliente->nombre }}</option>
                                    @endforeach
                                    <!-- Agrega más ítems según las columnas de tu tabla -->
                                    </select>                            
                            </div>
                            <div class="filtro d-flex flex-column" >
                                <label class=""  id="estado"  >
                                Estado
                                </label>
                                <select class="text-white bg-secondary rounded p-1" id="clientesSelect"   wire:model="estadoSeleccionado" >
                                    <option value='-1' >Todos</option>
                                    <option value='vencidas' >Vencidas</option>
                                    <option value='pendientes' >Pendientes</option>
                                    <option value='pagadas' >Pagadas</option>
                                    <!-- Agrega más ítems según las columnas de tu tabla -->
                                    </select>                            
                            </div>
                            
                        </div>
                        @if(count($arrFiltrado) > 0)
                            <p>Filtrando por: @if(isset($arrFiltrado[1])) Comerciales @endif  @if(isset($arrFiltrado[2])) Delegaciones @endif  @if(isset($arrFiltrado[3])) Cliente @endif @if(isset($arrFiltrado[4])) Estado @endif</p>
                        @endif
                        <button class="btn btn-primary" wire:click="limpiarFiltros()"  @if($comercialSeleccionadoId == -1 && $delegacionSeleccionadaCOD == -1 && $clienteSeleccionadoId == -1 && $estadoSeleccionado == -1 ) 
                        style="display:none"
                         @endif>Eliminar Filtros</button>

                    </div>
                    <button class="btn btn-primary" onclick="descargarFacturas()">Descargar seleccionados</button>
                    @if (isset($facturas) && count($facturas) > 0)

                            <!-- Aquí comienza el botón desplegable para filtrar por columna -->
                        <div id="Botonesfiltros" class="d-flex gap-2">
                            <div class="dropdown ">
                                <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                Filtrar por Columna
                                </button>
                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                    <a class="dropdown-item" href="#" data-column="0">Número</a>
                                    <a class="dropdown-item" href="#" data-column="1">P.asociado</a>
                                    <a class="dropdown-item" href="#" data-column="2">Cliente</a>
                                    <a class="dropdown-item" href="#" data-column="3">Total</a>
                                    <a class="dropdown-item" href="#" data-column="4">M.pago</a>
                                    <!-- Agrega más ítems según las columnas de tu tabla -->
                                </div>
                                <!-- Aquí termina el botón desplegable -->
                                <button class="btn btn-primary ml-2" id="clear-filter">Eliminar Filtro</button>
                            </div>
                            
                        </div>
                        
                        <div class="col-md-12 mt-4" x-data="{}" x-init="$nextTick(() => {
                            $('#datatable-buttons').DataTable({
                                responsive: true,
                                layout: {
                                    topStart: 'buttons'
                                },
                                lengthChange: false,
                                pageLength: 30,
                                buttons: ['copy', 'excel', 'pdf', 'colvis'],
                                language: {
                                    'lengthMenu': 'Mostrar _MENU_ registros por página',
                                    'zeroRecords': 'No se encontraron registros',
                                    'info': 'Mostrando página _PAGE_ de _PAGES_',
                                    'infoEmpty': 'No hay registros disponibles',
                                    'infoFiltered': '(filtrado de _MAX_ total registros)',
                                    'search': 'Buscar:',
                                },
                        
                                                });
                                            })"
                                            wire:key='{{ rand() }}'>
                        <table id="datatable-buttons" class="table table-striped table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;" wire:key='{{ rand() }}'>
                            <thead>
                                    <tr>
                                        <th scope="col">Descarga</th>
                                        <th scope="col">Número</th>
                                        <th scope="col">P.asociado</th>
                                        <th scope="col">Comercial</th>
                                        <th scope="col">Delegacion</th>
                                        <th scope="col">Cliente</th>
                                        <th scope="col">F.emisión</th>
                                        <th scope="col">F.vencimiento</th>
                                        <th scope="col">Importe</th>
                                        <th scope="col">IVA</th>
                                        <th scope="col">Total(Con IVA)</th>
                                        <th scope="col">M.pago</th>
                                        <th scope="col">Estado</th>
                                        <th scope="col">Acciones</th>
                                    </tr>
                            </thead>
                                <tbody>
                                    
                                    @foreach ($facturas as $key=>$fact)
                                        
                                        <tr>
                                            <td>
                                                <input type="checkbox" onclick="anadirArray({{ $fact->id }})" value="{{ $fact->id }}">
                                            </td>
                                            <td>{{ $fact->numero_factura }}</td>
                                            @if ($fact->pedido_id == 0 || $pedidos->where('id', $fact->pedido_id) == null)
                                                <td>Sin pedido</td>
                                            @else
                                                <td><a href="{{ route('pedidos.edit', ['id' => $fact->pedido_id]) }}"
                                                        class="btn btn-primary" target="_blank"> &nbsp;Pedido
                                                        {{ $fact->pedido_id }}</a></td>
                                            @endif
                                        
                                            <td>{{ $this->getComercial($fact->cliente_id)}}</td>
                                            <td>{{ $this->getDelegacion($fact->cliente_id)}}</td>

                                            <td>{{ $this->getCliente($fact->cliente_id)->nombre}}</td>

                                            <td>{{ $fact->fecha_emision }}</td>
                                            <td>@if((new DateTime($fact->fecha_vencimiento)) <= (new DateTime()) && $fact->estado != 'Pagado')
                                                    <span class="badge badge-danger">{{ $fact->fecha_vencimiento }}</span>
                                                @elseif($fact->estado == 'Pagado')
                                                    <span class="badge badge-success">{{ $fact->fecha_vencimiento }}</span>
                                                @else
                                                    <span class="badge badge-info">{{ $fact->fecha_vencimiento }}</span>
                                                @endif
                                            </td>
                                                    <td>{{number_format( $fact->precio ,2) }}€</td>
                                                    <td>
                                                        @if($fact->iva !== null)
                                                            {{ $fact->iva }}
                                                        @else
                                                            {{number_format(($fact->precio) * 0.21, 2)}}
                                                        @endif
                                                        € 
                                                    </td>
                                                    <td>
                                                        @if($fact->total !== null )
                                                            {{ $fact->total }}
                                                        @else
                                                            {{number_format(($fact->precio) * 1.21, 2)}}
                                                        @endif
                                                            €
                                                    </td>
                                            
                                            <td >
                                                @switch($fact->metodo_pago)
                                                    @case("giro_bancario")
                                                        Giro Bancario
                                                        @break
                                                    @case("confirming")
                                                        Confirming
                                                        @break
                                                    @case("transferencia")
                                                        Transferencia
                                                        @break
                                                    @case("pagare")
                                                        Pagaré
                                                        @break
                                                    @case("otros")
                                                        Otros
                                                        @break
                                                    @default
                                                    {{ $fact->metodo_pago }}
                                                @endswitch
                                                
                                            </td>
                                            <td>@switch($fact->estado)
                                                @case('Pendiente')
                                                <span class="badge badge-warning">{{ $fact->estado }}</span>
                                                    @break
                                                @case('Pagado')
                                                <span class="badge badge-success">{{ $fact->estado }}</span>
                                                    @break
                                                @case('Cancelado')
                                                <span class="badge badge-danger">{{ $fact->estado }}</span>
                                                    @break
                                                @default
                                                <span class="badge badge-info">{{ $fact->estado }}</span>
                                            @endswitch</td>
                                            <td> 
                                                <a href="facturas-edit/{{ $fact->id }}" class="btn btn-primary botones">
                                                    @if(Auth::user()->role == 3)
                                                        Ver
                                                    @else
                                                        Ver/Editar
                                                    @endif
                                                </a>
                                                <button  onclick="descargarFactura({{ $fact->id }}, true)" class="btn btn-primary botones" style="color: white;">Factura Con IVA</button>
                                                <button  onclick="descargarFactura({{ $fact->id }}, false)" class="btn btn-primary botones" style="color: white;">Factura Sin IVA</button>
                                                <button  onclick="mostrarAlbaran({{ $fact->id }}, true)" class="btn btn-primary botones" style="color: white;">Albarán</button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="8"></td>
                                        <td><strong>Total Importe</strong></td>
                                        <td><strong>Total Iva</strong></td>
                                        <td><strong>Total Con Iva</strong></td>
                                        <!-- Ajusta el colspan según el número de columnas en tu tabla -->
                                        <td colspan="2"></td>
                                    </tr>
                                    <tr>
                                        <td colspan="8"></td>
                                        <td><strong>{{ $totalImportes }}€</strong></td>
                                        <td><strong>{{ $totalIva }}€</strong></td>
                                        <td><strong>{{ $totalesConIva }}€</strong></td>
                                        <!-- Ajusta el colspan según el número de columnas en tu tabla -->
                                        <td colspan="2"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @else
                        <h6 class="text-center">No tenemos ninguna factura</h6>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@section('scripts')
    <script>

        //ready
        $(document).ready(function() {
            console.log('ready')    ;
            
            
            // $('#clientesSelect').on('change', function() {
            //     $('#datatable-buttons').DataTable().destroy();
            // });

            // $('#comercialesSelect').on('change', function() {
            //     $('#datatable-buttons').DataTable().destroy();
            // });

            // $('#delegacionesSelect').on('change', function() {
            //     $('#datatable-buttons').DataTable().destroy();
            // });

            $('#clear').on('click', function() {
                //console.log('clear')
                //$('#datatable-buttons').DataTable().destroy();
                window.livewire.emit('limpiarFiltros');
            });

            

        });

        let arrDescargas = [];
        function anadirArray(id) {
            //si el id no esta en el array lo añade, si esta lo elimina
            if(arrDescargas.includes(id)){
                arrDescargas = arrDescargas.filter(item => item !== id);
            }else{
                arrDescargas.push(id);
            }
            console.log(arrDescargas);
        }   

    //  livewire.on('actualizarTablaAntes', ()=>{
        
    //     $('#datatable-buttons').DataTable().destroy();
    //     //console.log('destruido')

    //  })

    //  livewire.on('actualizarTablaDespues', () =>{
        
    //         $('#datatable-buttons').DataTable({
    //     layout: {
    //     topStart: 'buttons'
    // },
    //     lengthChange: false,
    //     pageLength: 30,
    //     buttons: ['copy', 'excel', 'pdf', 'colvis'],
    //     responsive: true,
    //     "language": {
    //         "lengthMenu": "Mostrando _MENU_ registros por página",
    //         "zeroRecords": "Nothing found - sorry",
    //         "info": "Mostrando página _PAGE_ of _PAGES_",
    //         "infoEmpty": "No hay registros disponibles",
    //         "infoFiltered": "(filtrado de _MAX_ total registros)",
    //         "search": "Buscar:",
    //         "paginate": {
    //             "first": "Primero",
    //             "last": "Ultimo",
    //             "next": "<i class='fa-solid fa-arrow-right w-100'></i>",
    //             "previous": "<i class='fa-solid fa-arrow-left w-100'></i>"
    //         },
    //         "zeroRecords": "No se encontraron registros coincidentes",
    //     }
    // });
               
    //  })
    

     function descargarFacturas(){
        //console.log($array);
        //$('#datatable-buttons').DataTable().destroy();
        $array = arrDescargas;
        console.log($array, 'array');
        window.livewire.emit('descargarFacturas', $array);
     }
  
    function descargarFactura(id, conIva) {
        // Suponiendo que tu descarga se realiza aquí
        window.livewire.emit('pdf', id, conIva);
        setTimeout(() => {
            location.reload()
        }, 5000);
    }
    function mostrarAlbaran(id, conIva) {
        // Suponiendo que tu descarga se realiza aquí
        window.livewire.emit('albaran', id, conIva);
        setTimeout(() => {
            location.reload()
        }, 5000);
    }
    </script>
<script src="../assets/js/jquery.slimscroll.js"></script>
<link href="https://cdn.datatables.net/v/bs5/jq-3.7.0/jszip-3.10.1/dt-2.0.3/b-3.0.1/b-colvis-3.0.1/b-html5-3.0.1/b-print-3.0.1/r-3.0.1/datatables.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/v/bs5/jq-3.7.0/jszip-3.10.1/dt-2.0.3/b-3.0.1/b-colvis-3.0.1/b-html5-3.0.1/b-print-3.0.1/r-3.0.1/datatables.min.js"></script>
<!-- Responsive examples -->
{{-- <script src="../assets/pages/datatables.init.js"></script> --}}

@endsection
