// finanzas-data-provider.js
export class FinanzasDataProvider {
    constructor(data) {
        this.data = data || {};
    }
    getIncomeData() {
        return this.data.ingresos || { labels: [], data: [] };
    }
    getExpensesData() {
        return this.data.gastos || { labels: [], data: [] };
    }
    getComparisonData() {
        return this.data.comparacion || { labels: [], actual: [], anterior: [] };
    }
}