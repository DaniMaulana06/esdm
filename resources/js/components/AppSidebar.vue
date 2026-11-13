<script setup lang="ts">
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarRail,
} from '@/components/ui/sidebar';
import type { SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { Building2, Home, NotepadText, UserRoundCog } from 'lucide-vue-next';
import { computed, type Component } from 'vue';
import AppLogo from './AppLogo.vue';
import NavCollapsible from './NavCollapsible.vue';
import Separator from './ui/separator/Separator.vue';

interface NavChild {
    title: string;
    href: string;
    roles: string[];
}
interface NavGroup {
    title: string;
    icon: Component;
    href?: string;
    roles?: string[]; // untuk menu tunggal
    items?: NavChild[]; // untuk grup
}

const page = usePage<SharedData>();

const navMain: NavGroup[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: Home,
        roles: ['admin', 'staf_dinas', 'operator_bku'],
    },
    {
        title: 'Data Master',
        icon: Building2,
        items: [
            { title: 'BKU', href: '/bku', roles: ['admin', 'staf_dinas'] },
            { title: 'Sumur', href: '/sumur', roles: ['admin', 'staf_dinas', 'operator_bku'] },
            { title: 'Kontrak', href: '/kontrak', roles: ['admin', 'staf_dinas'] },
            { title: 'BKU Kontrak', href: '/bku-kontrak', roles: ['admin', 'staf_dinas'] },
        ],
    },
    {
        title: 'Laporan',
        icon: NotepadText,
        items: [
            { title: 'Laporan Harian', href: '/laporan-harian', roles: ['admin', 'staf_dinas', 'operator_bku'] },
            { title: 'Justifikasi', href: '/justifikasi', roles: ['admin', 'staf_dinas'] },
        ],
    },
    {
        title: 'Manajemen User',
        href: '/users',
        icon: UserRoundCog,
        roles: ['admin'],
    },
];

const visibleMenuItems = computed(() => {
    const role = page.props.auth.user?.role ?? '';

    return navMain
        .map((group) =>
            group.items ? { ...group, items: group.items.filter((c) => c.roles.includes(role)) } : group,
        )
        .filter((group) => (group.items ? group.items.length > 0 : group.roles?.includes(role)));
});
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavCollapsible :items="visibleMenuItems" />
        </SidebarContent>

        <Separator/>
        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
        <SidebarRail />
    </Sidebar>
    <slot />
</template>