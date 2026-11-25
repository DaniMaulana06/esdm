<script setup lang="ts">
import DashboardDailyProductionCard from '@/components/dashboard/DashboardDailyProductionCard.vue';
import DashboardFilterCard from '@/components/dashboard/DashboardFilterCard.vue';
import DashboardHeader from '@/components/dashboard/DashboardHeader.vue';
import DashboardKpiAdmin from '@/components/dashboard/DashboardKpiAdmin.vue';
import DashboardKpiOperator from '@/components/dashboard/DashboardKpiOperator.vue';
import DashboardLatestReportsCard from '@/components/dashboard/DashboardLatestReportsCard.vue';
import DashboardMissingDatesCard from '@/components/dashboard/DashboardMissingDatesCard.vue';
import DashboardMissingReportHistoryCard from '@/components/dashboard/DashboardMissingReportHistoryCard.vue';
import DashboardMissingReportsCard from '@/components/dashboard/DashboardMissingReportsCard.vue';
import DashboardMonthlyComparisonCard from '@/components/dashboard/DashboardMonthlyComparisonCard.vue';
import DashboardProductionByBkuCard from '@/components/dashboard/DashboardProductionByBkuCard.vue';
import DashboardProductionLiftingTableCard from '@/components/dashboard/DashboardProductionLiftingTableCard.vue';
import DashboardReportingStatusCard from '@/components/dashboard/DashboardReportingStatusCard.vue';
import type { DashboardProps, FilterPayload } from '@/components/dashboard/types';
import AppLayout from '@/layouts/AppLayout.vue';
import { SharedData } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<DashboardProps>();

const page = usePage<SharedData>();
const user = computed(() => page.props.auth.user);

const isStafEsdmOrAdmin = computed(() => {
    return user.value?.role === 'staf_dinas' || user.value?.role === 'admin';
});

const isBku = computed(() => {
    return user.value?.role === 'operator_bku';
});

const applyFilter = (payload: FilterPayload) => {
    router.get(
        route('dashboard'),
        { ...payload },
        {
            preserveScroll: true,
            preserveState: true,
            only: ['filters', 'stats', 'productionChart', 'productionLiftingChart', 'productionMonthlyChart', 'productionByBku'],
        },
    );
};

const resetFilter = () => {
    router.get(route('dashboard'), {}, { preserveScroll: true });
};

// cetak laporan
const printDashboard = () => {
    const url = route('dashboard.pdf', {
        start_date: props.filters.start_date,
        end_date: props.filters.end_date,
        bku_id: props.filters.bku_id,
        kontrak_id: props.filters.kontrak_id,
    });

    window.open(url, '_blank');
};
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout
        :breadcrumbs="[
            {
                title: 'Dashboard',
                href: route('dashboard'),
            },
        ]"
    >
        <div class="py-6">
            <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <DashboardHeader :show-print="isStafEsdmOrAdmin" @print="printDashboard" />

                <!-- KPI Admin or Staf -->
                <DashboardKpiAdmin v-if="!isBku" :stats="stats" />

                <!-- KPI Operator BKU -->
                <DashboardKpiOperator v-if="isBku" :stats="stats" :total-sumur="total_sumur" />

                <!-- Laporan Terlewat (Hanya Operator BKU) -->
                <DashboardMissingDatesCard v-if="isBku" :items="missingDatesForOperator" />

                <!-- Filter -->
                <DashboardFilterCard
                    v-if="!isBku"
                    :filters="filters"
                    :bkus="bkus"
                    :kontraks="kontraks"
                    :is-operator="false"
                    @apply="applyFilter"
                    @reset="resetFilter"
                />

                <DashboardFilterCard
                    v-if="isBku"
                    :filters="filters"
                    :bkus="bkus"
                    :kontraks="kontraks"
                    :is-operator="true"
                    @apply="applyFilter"
                    @reset="resetFilter"
                />

                <!-- Produksi per BKU + Status -->
                <div v-if="isStafEsdmOrAdmin" class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <DashboardProductionByBkuCard :items="productionByBku" />
                    <DashboardReportingStatusCard :status="reportingStatus" />
                </div>

                <!-- Grafik Produksi Harian -->
                <DashboardDailyProductionCard :items="productionChart" />

                <!-- Grafik Perbandingan Produksi & Lifting -->
                <DashboardMonthlyComparisonCard :items="productionMonthlyChart" />

                <!-- Produksi vs Lifting -->
                <DashboardProductionLiftingTableCard :items="productionLiftingChart" />

                <!-- BKU Belum Lengkap -->
                <DashboardMissingReportsCard v-if="isStafEsdmOrAdmin" :items="missingReports" />

                <!-- Riwayat BKU Belum Melaporkan -->
                <DashboardMissingReportHistoryCard v-if="isStafEsdmOrAdmin" :items="bkuBelumLapor" />

                <!-- Laporan Terbaru -->
                <DashboardLatestReportsCard v-if="isStafEsdmOrAdmin" :items="latestReports" />
            </div>
        </div>
    </AppLayout>
</template>
