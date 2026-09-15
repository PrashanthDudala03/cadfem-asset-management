@extends('layouts/default')

@section('title')
    Equipment Dashboard - CADFEM Asset Management
@stop

@section('content')
<div class="container-fluid" style="background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); min-height: 100vh; padding: 30px 0;">
    <div class="row mb-4 px-4">
        <div class="col-md-12">
            <h1 style="font-size: 36px; font-weight: 700; color: #003366; margin: 0;">
                <i class="fas fa-chart-line"></i> Equipment Dashboard
            </h1>
            <p style="color: #666; margin-top: 8px;">Real-time asset inventory</p>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="row mb-4 px-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div style="background: white; border-radius: 12px; padding: 24px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); border-left: 5px solid #003366;">
                <p style="margin: 0; color: #999; font-size: 12px; font-weight: 600;">TOTAL ASSETS</p>
                <h2 style="margin: 12px 0 0 0; font-size: 42px; font-weight: 800; color: #003366;">85</h2>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div style="background: white; border-radius: 12px; padding: 24px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); border-left: 5px solid #28a745;">
                <p style="margin: 0; color: #999; font-size: 12px; font-weight: 600;">ACTIVE ASSETS</p>
                <h2 style="margin: 12px 0 0 0; font-size: 42px; font-weight: 800; color: #28a745;">80</h2>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div style="background: white; border-radius: 12px; padding: 24px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); border-left: 5px solid #ffc107;">
                <p style="margin: 0; color: #999; font-size: 12px; font-weight: 600;">IN MAINTENANCE</p>
                <h2 style="margin: 12px 0 0 0; font-size: 42px; font-weight: 800; color: #ffc107;">0</h2>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div style="background: white; border-radius: 12px; padding: 24px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); border-left: 5px solid #dc3545;">
                <p style="margin: 0; color: #999; font-size: 12px; font-weight: 600;">RETIRED</p>
                <h2 style="margin: 12px 0 0 0; font-size: 42px; font-weight: 800; color: #dc3545;">5</h2>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="row mb-4 px-4">
        <div class="col-lg-6 mb-3">
            <div style="background: white; border-radius: 12px; padding: 24px; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
                <h5 style="margin-top: 0; color: #333; font-weight: 700;">Asset Status Distribution</h5>
                <div id="statusChart"></div>
            </div>
        </div>
        <div class="col-lg-6 mb-3">
            <div style="background: white; border-radius: 12px; padding: 24px; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
                <h5 style="margin-top: 0; color: #333; font-weight: 700;">Assets by Category</h5>
                <div id="categoryChart"></div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="row px-4">
        <div class="col-lg-12">
            <div style="background: white; border-radius: 12px; padding: 24px; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
                <h5 style="margin-top: 0; color: #333; font-weight: 700;">Sample Assets</h5>
                <table class="table table-hover">
                    <thead style="background: #f8f9fa;">
                        <tr>
                            <th>Asset Tag</th>
                            <th>Asset Name</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Location</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>FN-E16-02</strong></td>
                            <td>LENOVO E16G1</td>
                            <td><span style="background: #e8f4f8; color: #003366; padding: 4px 10px; border-radius: 20px;">Laptop</span></td>
                            <td><span style="background: #d4edda; color: #155724; padding: 4px 10px; border-radius: 20px;">Deployed</span></td>
                            <td>Hyderabad</td>
                        </tr>
                        <tr>
                            <td><strong>CFD-3680-04</strong></td>
                            <td>Dell T3680</td>
                            <td><span style="background: #e8f4f8; color: #003366; padding: 4px 10px; border-radius: 20px;">CPU</span></td>
                            <td><span style="background: #d4edda; color: #155724; padding: 4px 10px; border-radius: 20px;">Deployed</span></td>
                            <td>Hyderabad</td>
                        </tr>
                        <tr>
                            <td><strong>IT-HPZ2-02</strong></td>
                            <td>HP Series 5 Pro</td>
                            <td><span style="background: #e8f4f8; color: #003366; padding: 4px 10px; border-radius: 20px;">Monitor</span></td>
                            <td><span style="background: #d4edda; color: #155724; padding: 4px 10px; border-radius: 20px;">Deployed</span></td>
                            <td>Hyderabad</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/apexcharts/3.45.0/apexcharts.min.js"></script>

<script>
// Status Chart
var statusChart = new ApexCharts(document.querySelector("#statusChart"), {
    series: [80, 5],
    chart: {type: 'donut', height: 300},
    labels: ['Deployed', 'Retired'],
    colors: ['#28a745', '#dc3545'],
    plotOptions: {pie: {donut: {size: '75%'}}},
    legend: {position: 'bottom'}
});
statusChart.render();

// Category Chart
var categoryChart = new ApexCharts(document.querySelector("#categoryChart"), {
    series: [{name: 'Count', data: [13, 20, 20, 31]}],
    chart: {type: 'bar', height: 300},
    xaxis: {categories: ['Laptop', 'CPU', 'Monitor', 'Server']},
    colors: ['#003366'],
    plotOptions: {bar: {columnWidth: '55%', borderRadius: 4}},
    dataLabels: {enabled: false}
});
categoryChart.render();
</script>
@stop
