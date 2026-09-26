<script setup lang="ts">
import { Breadcrumb, BreadcrumbItem, BreadcrumbLink, BreadcrumbList, BreadcrumbPage, BreadcrumbSeparator } from '@/components/ui/breadcrumb';
import { SidebarTrigger } from '@/components/ui/sidebar';
import type { BreadcrumbItemType, SharedData, User } from '@/types';
import { usePage } from '@inertiajs/vue3';

defineProps<{
    breadcrumbs?: BreadcrumbItemType[];
}>();

const page = usePage<SharedData>();
const user = page.props.auth.user as User;

const formatRole = (role: string) => {
    switch (role) {
        case 'admin':
            return 'Admin';

        case 'staf_dinas':
            return 'Staf Dinas';

        case 'operator_bku':
            return 'Operator BKU';

        default:
            return role;
    }
};
</script>

<template>
    <header
        class="flex h-16 shrink-0 items-center justify-between border-b border-sidebar-border/70 px-6 transition-[width,height] ease-linear group-has-[[data-collapsible=icon]]/sidebar-wrapper:h-12 md:px-4">
        <!-- Kiri: Sidebar + Breadcrumb -->
        <div class="flex items-center gap-2">
            <SidebarTrigger class="-ml-1" />

            <template v-if="breadcrumbs && breadcrumbs.length > 0">
                <Breadcrumb>
                    <BreadcrumbList>
                        <template v-for="(item, index) in breadcrumbs" :key="index">
                            <BreadcrumbItem>
                                <template v-if="index === breadcrumbs.length - 1">
                                    <BreadcrumbPage>
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

        <div class="flex items-center gap-3">
            <div class="flex size-8 items-center justify-center rounded-full bg-gray-200 text-xl font-semibold">
                {{ user.name.charAt(0).toUpperCase() }}
            </div>

            <div class="hidden text-right sm:block">
                <p class="text-base font-medium">
                    <b>{{ user.name }}</b> | {{formatRole(user.role)}}
                </p>

                <p class="text-sm text-muted-foreground">
                    
                </p>
            </div>
        </div>
    </header>
</template>
