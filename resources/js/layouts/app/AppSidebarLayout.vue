<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import { SidebarInset } from '@/components/ui/sidebar';
import SidebarProvider from '@/components/ui/sidebar/SidebarProvider.vue';
import type { BreadcrumbItemType } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <AppShell variant="sidebar">
        <SidebarProvider>
            <AppSidebar />
            <SidebarInset class="min-h-svh bg-gray-50">
                <AppSidebarHeader :breadcrumbs="breadcrumbs" />
                <AppContent variant="sidebar">
                    <slot />
                </AppContent>
            </SidebarInset>
        </SidebarProvider>
    </AppShell>
</template>
