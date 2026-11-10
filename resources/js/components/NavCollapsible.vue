<script setup lang="ts">
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarMenuSub,
    SidebarMenuSubButton,
    SidebarMenuSubItem,
} from '@/components/ui/sidebar';
import { Link, usePage } from '@inertiajs/vue3';
import { ChevronRight } from 'lucide-vue-next';
import type { Component } from 'vue';

interface NavChild {
    title: string;
    href: string;
}
interface NavGroup {
    title: string;
    icon?: Component;
    href?: string;
    items?: NavChild[];
}

defineProps<{ items: NavGroup[] }>();

const page = usePage();

const isActive = (href: string) => {
    const current = page.url.split('?')[0];
    return current === href || current.startsWith(href + '/');
};

const isGroupOpen = (group: NavGroup) => !!group.items?.some((c) => isActive(c.href));
</script>

<template>
    <SidebarGroup>
        <SidebarGroupLabel>Menu</SidebarGroupLabel>
        <SidebarMenu>
            <template v-for="group in items" :key="group.title">
                <!-- Menu tunggal -->
                <SidebarMenuItem v-if="group.href">
                    <SidebarMenuButton as-child :tooltip="group.title" :is-active="isActive(group.href)">
                        <Link :href="group.href">
                            <component :is="group.icon" v-if="group.icon" />
                            <span>{{ group.title }}</span>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>

                <!-- Grup collapsible -->
                <Collapsible v-else as-child :default-open="isGroupOpen(group)" class="group/collapsible">
                    <SidebarMenuItem>
                        <CollapsibleTrigger as-child>
                            <SidebarMenuButton :tooltip="group.title">
                                <component :is="group.icon" v-if="group.icon" />
                                <span>{{ group.title }}</span>
                                <ChevronRight
                                    class="ml-auto transition-transform duration-200 group-data-[state=open]/collapsible:rotate-90"
                                />
                            </SidebarMenuButton>
                        </CollapsibleTrigger>
                        <CollapsibleContent>
                            <SidebarMenuSub>
                                <SidebarMenuSubItem v-for="child in group.items" :key="child.title">
                                    <SidebarMenuSubButton as-child :is-active="isActive(child.href)">
                                        <Link :href="child.href">
                                            <span>{{ child.title }}</span>
                                        </Link>
                                    </SidebarMenuSubButton>
                                </SidebarMenuSubItem>
                            </SidebarMenuSub>
                        </CollapsibleContent>
                    </SidebarMenuItem>
                </Collapsible>
            </template>
        </SidebarMenu>
    </SidebarGroup>
</template>