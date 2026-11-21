<script setup lang="ts">
import { Breadcrumb, BreadcrumbItem, BreadcrumbLink, BreadcrumbList, BreadcrumbPage, BreadcrumbSeparator } from '@/components/ui/breadcrumb';
import { SidebarTrigger } from '@/components/ui/sidebar';
import type { BreadcrumbItemType, SharedData } from '@/types';
import NavUser from './NavUser.vue';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Building2, CircleUserRound } from 'lucide-vue-next';

defineProps<{
    breadcrumbs?: BreadcrumbItemType[];
}>();

const page = usePage<SharedData>();
const user = computed(() => page.props.auth.user);
</script>

<template>
    <header
        class="flex h-16 shrink-0 items-center justify-between border-b border-sidebar-border/70 shadow-md px-6 transition-[width,height] ease-linear group-has-[[data-collapsible=icon]]/sidebar-wrapper:h-12 md:px-4">
        <!-- Kiri: Sidebar + Breadcrumb -->
        <div class="flex items-center gap-2">
            <SidebarTrigger class="-ml-1" />

            <template v-if="breadcrumbs && breadcrumbs.length > 0">
                <Breadcrumb>
                    <BreadcrumbList>
                        <template v-for="(item, index) in breadcrumbs" :key="index">
                            <BreadcrumbItem>
                                <template v-if="index === breadcrumbs.length - 1">
                                    <BreadcrumbPage class="text-black">
                                        {{ item.title }}
                                    </BreadcrumbPage>
                                </template>

                                <template v-else>
                                    <BreadcrumbLink :href="item.href">
                                        {{ item.title }}
                                    </BreadcrumbLink>
                                </template>
                            </BreadcrumbItem>

                            <BreadcrumbSeparator v-if="index !== breadcrumbs.length - 1" />
                        </template>
                    </BreadcrumbList>
                </Breadcrumb>
            </template>
        </div>

        <!-- BKU - Nama User -->
        <div class="flex min-w-0 flex-nowrap items-center gap-3">
            <div v-if="user?.role === 'operator_bku' && user.bku"
                class="flex shrink-0 items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-gray-800">
                <Building2 class="h-5 w-5 shrink-0 text-yellow-600" :stroke-width="2" />

                <span class="whitespace-nowrap">
                    {{ user.bku }}
                </span>
            </div>

            <NavUser class="shrink-2" />
        </div>

        <!-- <div class="flex items-center gap-3">

            <NavUser />
        </div> -->
    </header>
</template>
