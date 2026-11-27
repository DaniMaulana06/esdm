export interface Bku {
    id: number;
    nama: string;
}

export interface Kontrak {
    id: number;
    nama: string;
}

export interface Stats {
    total_bku: number;
    total_kontrak: number;
    average_produksi: number;
    average_lifting: number;
}

export interface ProductionChartItem {
    tanggal: string;
    total_produksi: number;
}

export interface ProductionLiftingItem {
    tanggal: string;
    kontrak_id: number;
    kontrak: string;
    total_produksi: number;
    total_lifting: number;
}

export interface ProductionByBku {
    id: number;
    nama: string;
    total_produksi: number;
}

export interface ReportingStatus {
    total_bku: number;
    sudah_melapor: number;
    belum_melapor: number;
    percentage: number;
}

export interface ReportContract {
    id: number;
    nama: string;
}

export interface MissingReport {
    id: number;
    nama: string;
    missing_count: number;
    contracts: ReportContract[];
}

export interface LatestReport {
    id: number;
    tanggal: string;
    bku: string;
    kontrak: string;
    total_produksi: number;
    total_lifting: number;
}

export interface BkuBelumLapor {
    tanggal: string;
    bku_id: number;
    bku: string;
    missing_count: number;
    contracts: ReportContract[];
}

export interface MissingDateForOperator {
    tanggal: string;
    missing_count: number;
    contracts: ReportContract[];
}

export interface ReportingDisciplineItem {
    bku_id: number;
    nama: string;
    total_hari_efektif: number;
    terlambat: number;
    tidak_melapor: number;
    tepat_waktu: number;
    persentase_tepat_waktu: number;
}

export interface ProductionMonthlyItem {
    bulan: string; // "2026-11"
    total_produksi: number;
    total_lifting: number;
}

export interface ChartData {
    index: number;
    label: string;
    total_produksi: number;
    total_lifting: number;
}

export interface DashboardFilters {
    start_date: string;
    end_date: string;
    bku_id: number | null;
    kontrak_id: number | null;
}

export interface FilterPayload {
    start_date: string;
    end_date: string;
    bku_id?: number;
    kontrak_id?: number;
}

export interface DashboardProps {
    stats: Stats;
    filters: DashboardFilters;
    bkus: Bku[];
    kontraks: Kontrak[];
    productionChart: ProductionChartItem[];
    productionLiftingChart: ProductionLiftingItem[];
    productionMonthlyChart: ProductionMonthlyItem[];
    productionByBku: ProductionByBku[];
    reportingStatus: ReportingStatus;
    missingReports: MissingReport[];
    bkuBelumLapor: BkuBelumLapor[];
    latestReports: LatestReport[];
    missingDatesForOperator: MissingDateForOperator[];
    disiplinPelaporan: ReportingDisciplineItem[];
    total_sumur?: number;
}
