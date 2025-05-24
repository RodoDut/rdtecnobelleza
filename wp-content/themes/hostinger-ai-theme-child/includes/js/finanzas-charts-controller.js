/*
    * FinanzasChartsController.js
    * This module controls the rendering of financial charts using Chart.js.
    * It initializes the charts with data provided by the FinanzasDataProvider.
    * It uses the FinanzasChartService to handle the rendering and destruction of charts.
*/
// finanzas-charts-controller.js
import { FinanzasDataProvider } from './finanzas-data-provider.js';
import { FinanzasChartService } from './finanzas-chart-service.js';

export class FinanzasChartsController {
    constructor(data, ChartLib) {
        this.dataProvider = new FinanzasDataProvider(data);
        this.chartService = new FinanzasChartService(ChartLib);
    }
    initCharts(containerId) {
        this.chartService.destroyAll();
        // Ingresos
        const ingresos = this.dataProvider.getIncomeData();
        const incomeCtx = document.getElementById('income-chart-' + containerId)?.getContext('2d');
        if (incomeCtx) {
            this.chartService.charts.income = this.chartService.renderBarChart(
                incomeCtx, ingresos.labels, ingresos.data, 'Ingresos',
                { backgroundColor: 'rgba(75,192,192,0.2)', borderColor: 'rgba(75,192,192,1)', borderWidth: 1 }
            );
        }
        // Gastos
        const gastos = this.dataProvider.getExpensesData();
        const expensesCtx = document.getElementById('expenses-chart-' + containerId)?.getContext('2d');
        if (expensesCtx) {
            this.chartService.charts.expenses = this.chartService.renderBarChart(
                expensesCtx, gastos.labels, gastos.data, 'Gastos',
                { backgroundColor: 'rgba(255,99,132,0.2)', borderColor: 'rgba(255,99,132,1)', borderWidth: 1 }
            );
        }
        // Comparación
        const comparacion = this.dataProvider.getComparisonData();
        const comparisonCtx = document.getElementById('comparison-chart-' + containerId)?.getContext('2d');
        if (comparisonCtx) {
            this.chartService.charts.comparison = this.chartService.renderLineChart(
                comparisonCtx, comparacion.labels, [
                    { label: 'Este año', data: comparacion.actual, borderColor: 'rgba(75,192,192,1)', backgroundColor: 'rgba(75,192,192,0.2)', tension: 0.1 },
                    { label: 'Año anterior', data: comparacion.anterior, borderColor: 'rgba(153,102,255,1)', backgroundColor: 'rgba(153,102,255,0.2)', tension: 0.1 }
                ]
            );
        }
    }
}