<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { Link, usePage } from '@inertiajs/vue3';
import type { NavItem, SharedData } from '@/types';
import { 
    LayoutGrid, 
    Home,
    Building2, 
    LandPlot,
    Signature,
    NotepadText,
    User2
} from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';
import { computed } from 'vue';

const page = usePage<SharedData>();

const mainNavItems: (NavItem & { roles: string[]})[]= [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: Home,
        roles: ['admin', 'staf_dinas', 'operator_bku'],
    },
    {
        title: 'BKU',
        href: '/bku',
        icon: Building2,
        roles: ['admin', 'staf_dinas'],
    },
    {
        title: 'Sumur',
        href: '/sumur',
        icon: LandPlot,
        roles: ['admin', 'staf_dinas','operator_bku'],
    },
    {
        title: 'Kontrak',
        href: '/kontrak',
        icon: Signature,
        roles: ['admin', 'staf_dinas'],
    },
    {
        title: 'BKU Kontrak',
        href: '/bku-kontrak',
        icon: LayoutGrid,
        roles: ['admin', 'staf_dinas'],
    },

    {
        title: 'Laporan Harian',
        href: '/laporan-harian',
        icon: NotepadText,
        roles: ['admin', 'staf_dinas', 'operator_bku'],
    },
    
    {
        title: 'Manajemen User',
        href: '/users',
        icon: User2,
        roles: ['admin'],
    },
];
const visibleMenuItems = computed(() => {
    const role = page.props.auth.user?.role;

    return mainNavItems.filter((item) =>
        item.roles.includes(role)
    );
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
            <NavMain :items="visibleMenuItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
