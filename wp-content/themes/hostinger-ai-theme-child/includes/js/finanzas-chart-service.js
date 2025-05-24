// finanzas-chart-service.js
/*
    * FinanzasChartService.js
    * This module provides a service for rendering charts using Chart.js.
    * It includes methods for rendering bar and line charts, and for destroying all charts.
    * Encapsulates the Chart.js library to provide a simplified interface for creating charts.
    * @module FinanzasChartService
    */
export class FinanzasChartService {
    constructor(chartLib) {
        this.Chart = chartLib;
        this.charts = {};
    }
    renderBarChart(ctx, labels, data, label, colors) {
        return new this.Chart(ctx, {
            type: 'bar',
            data: { labels, datasets: [{ label, data, ...colors }] },
            options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } } }
        });
    }
    renderLineChart(ctx, labels, datasets) {
        return new this.Chart(ctx, {
            type: 'line',
            data: { labels, datasets },
            options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } } }
        });
    }
    destroyAll() {
        Object.values(this.charts).forEach(chart => chart.destroy());
        this.charts = {};
    }
}