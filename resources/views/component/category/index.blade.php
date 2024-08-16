@extends('layouts.layout')
@push('css-scripts')
<link href="{{asset('hub/assets/plugins/datatables.net-bs5/css/dataTables.bootstrap5.min.css')}}" rel="stylesheet">
<link href="{{asset('hub/assets/plugins/datatables.net-buttons-bs5/css/buttons.bootstrap5.min.css')}}" rel="stylesheet">
<link href="{{asset('hub/assets/plugins/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css')}}" rel="stylesheet">
@endpush

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-10">
                <div class="row">
                    <div class="col-xl-9">
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="#">TABLES</a></li>
                            <li class="breadcrumb-item active">TABLE PLUGINS</li>
                        </ul>
                        <h1 class="page-header">
                            Table Plugins <small>page header description goes here...</small>
                        </h1>
                        <hr class="mb-4">
                        <div id="datatable" class="mb-5">
                            <h4>Datatable</h4>
                            <p>DataTables is a plug-in for the jQuery Javascript library. It is a highly flexible tool, built upon the foundations of progressive enhancement, that adds all of these advanced features to any HTML table. Please read the <a href="https://datatables.net/" target="_blank">official documentation</a> for the full list of options.</p>
                            <div class="card">
                                <div class="card-body">
                                    <table id="datatableDefault" class="table text-nowrap w-100">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Name</th>
                                                <th>Position</th>
                                                <th>Office</th>
                                                <th>Age</th>
                                                <th>Start Date</th>
                                                <th>Salary</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>1.</td>
                                                <td>Tiger Nixon</td>
                                                <td>System Architect</td>
                                                <td>Edinburgh</td>
                                                <td>61</td>
                                                <td>2024/04/25</td>
                                                <td>$320,800</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="card-arrow">
                                    <div class="card-arrow-top-left"></div>
                                    <div class="card-arrow-top-right"></div>
                                    <div class="card-arrow-bottom-left"></div>
                                    <div class="card-arrow-bottom-right"></div>
                                </div>
                                <div class="hljs-container">
                                    <pre><code class="xml" data-url="assets/data/table-plugins/code-1.json"></code></pre>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3">
                        <nav id="sidebar-bootstrap" class="navbar navbar-sticky d-none d-xl-block">
                            <nav class="nav">
                                <a class="nav-link" href="#datatable" data-toggle="scroll-to">Datatable</a>
                                <a class="nav-link" href="#bootstrapTable" data-toggle="scroll-to">Bootstrap table</a>
                            </nav>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @push('pages-scripts')
    <script src="{{asset('hub/assets/plugins/datatables.net/js/dataTables.min.js')}}" type="3e072b31e4d62a351cb180e3-text/javascript"></script>
    <script src="{{asset('hub/assets/plugins/datatables.net-bs5/js/dataTables.bootstrap5.min.js')}}" type="3e072b31e4d62a351cb180e3-text/javascript"></script>
    <script src="{{asset('hub/assets/plugins/datatables.net-buttons/js/dataTables.buttons.min.js')}}" type="3e072b31e4d62a351cb180e3-text/javascript"></script>
    <script src="{{asset('hub/assets/plugins/datatables.net-buttons/js/buttons.colVis.min.js')}}" type="3e072b31e4d62a351cb180e3-text/javascript"></script>
    <script src="{{asset('hub/assets/plugins/datatables.net-buttons/js/buttons.flash.min.js')}}" type="3e072b31e4d62a351cb180e3-text/javascript"></script>
    <script src="{{asset('hub/assets/plugins/datatables.net-buttons/js/buttons.html5.min.js')}}" type="3e072b31e4d62a351cb180e3-text/javascript"></script>
    <script src="{{asset('hub/assets/plugins/datatables.net-buttons/js/buttons.print.min.js')}}" type="3e072b31e4d62a351cb180e3-text/javascript"></script>
    <script src="{{asset('hub/assets/plugins/datatables.net-buttons-bs5/js/buttons.bootstrap5.min.js')}}" type="3e072b31e4d62a351cb180e3-text/javascript"></script>
    <script src="{{asset('hub/assets/plugins/datatables.net-responsive/js/dataTables.responsive.min.js')}}" type="3e072b31e4d62a351cb180e3-text/javascript"></script>
    <script src="{{asset('hub/assets/plugins/datatables.net-responsive-bs5/js/responsive.bootstrap5.min.js')}}" type="3e072b31e4d62a351cb180e3-text/javascript"></script>
    <script src="{{asset('hub/assets/plugins/bootstrap-table/dist/bootstrap-table.min.js')}}" type="3e072b31e4d62a351cb180e3-text/javascript"></script>
    <script src="{{asset('hub/assets/js/demo/table-plugins.demo.js')}}" type="3e072b31e4d62a351cb180e3-text/javascript"></script>
    <script src="{{asset('hub/assets/js/demo/sidebar-scrollspy.demo.js')}}" type="3e072b31e4d62a351cb180e3-text/javascript"></script>
    @endpush

    @endsection