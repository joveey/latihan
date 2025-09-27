@extends('layouts.app')

@section('title', 'Dashboard Analitik')

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        {{-- Kartu untuk Grafik Tipe Langganan --}}
        <div class="bg-white p-6 rounded-lg shadow-md transition hover:shadow-xl">
            <canvas id="subscriptionChart"></canvas>
        </div>

        {{-- Kartu untuk Grafik Churn --}}
        <div class="bg-white p-6 rounded-lg shadow-md transition hover:shadow-xl">
            <canvas id="churnChart"></canvas>
        </div>

        {{-- Kartu untuk Grafik Negara (memanjang) --}}
        <div class="bg-white p-6 rounded-lg shadow-md lg:col-span-2 transition hover:shadow-xl">
            <canvas id="countryChart"></canvas>
        </div>
    </div>
@endsection

@push('scripts')
    {{-- Memuat library Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // =========================================================
        // PERBAIKAN GRAFIK DI SINI
        // =========================================================

        // Ambil data dari controller
        const subscriptionData = @json($subscriptionData);
        const churnData = @json($churnData);
        const countryData = @json($countryData);

        // Palet warna yang lebih bagus dan konsisten
        const colorPalette = [
            '#4f46e5', // Indigo
            '#14b8a6', // Teal
            '#f59e0b', // Amber
            '#e11d48', // Rose
            '#3b82f6', // Blue
            '#8b5cf6', // Violet
            '#22c55e', // Green
            '#ef4444', // Red
            '#64748b', // Slate
            '#f97316'  // Orange
        ];

        // 1. Grafik Tipe Langganan (Pie Chart)
        const subscriptionCtx = document.getElementById('subscriptionChart').getContext('2d');
        new Chart(subscriptionCtx, {
            type: 'pie',
            data: {
                labels: Object.keys(subscriptionData),
                datasets: [{
                    data: Object.values(subscriptionData),
                    backgroundColor: colorPalette,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'top' },
                    title: { display: true, text: 'Distribusi Tipe Langganan', font: { size: 18 } },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                let value = context.raw || 0;
                                let percentage = ((value / context.chart.getDatasetMeta(0).total) * 100).toFixed(1);
                                return ` ${label}: ${value} pengguna (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });

        // 2. Grafik Churn (Doughnut Chart)
        const churnCtx = document.getElementById('churnChart').getContext('2d');
        new Chart(churnCtx, {
            type: 'doughnut',
            data: {
                labels: Object.keys(churnData),
                datasets: [{
                    data: Object.values(churnData),
                    backgroundColor: ['#22c55e', '#ef4444'], // Green for Retained, Red for Churned
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'top' },
                    title: { display: true, text: 'Analisis Churn Pengguna', font: { size: 18 } },
                     tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                let value = context.raw || 0;
                                let percentage = ((value / context.chart.getDatasetMeta(0).total) * 100).toFixed(1);
                                return ` ${label}: ${value} pengguna (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });

        // 3. Grafik Negara (Bar Chart Horizontal)
        const countryCtx = document.getElementById('countryChart').getContext('2d');
        new Chart(countryCtx, {
            type: 'bar',
            data: {
                labels: Object.keys(countryData),
                datasets: [{
                    label: 'Jumlah Pengguna',
                    data: Object.values(countryData),
                    backgroundColor: colorPalette,
                    borderColor: 'white',
                    borderWidth: 2
                }]
            },
            options: {
                indexAxis: 'y', // Membuat bar chart menjadi horizontal
                responsive: true,
                plugins: {
                    legend: { display: false },
                    title: { display: true, text: 'Top 10 Negara Pengguna', font: { size: 18 } },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return ` Jumlah Pengguna: ${context.raw}`;
                            }
                        }
                    }
                },
                scales: {
                    x: { beginAtZero: true }
                }
            }
        });
    </script>
@endpush