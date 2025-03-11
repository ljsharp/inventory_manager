import type { PageProps } from '@inertiajs/core';
import type { LucideIcon } from 'lucide-vue-next';
import type { Config } from 'ziggy-js';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: string;
    icon?: LucideIcon;
    isActive?: boolean;
}

export interface SharedData extends PageProps {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    ziggy: Config & { location: string };
}

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
}

export type BreadcrumbItemType = BreadcrumbItem;

export interface Warehouse {
    id?: number;
    name?: string;
    location?: string;
    contact_info?: string;
    capacity?: number;
}

export interface Product {
    id?: number;
    name: string;
    description?: string;
    sku?: string;
    category_id: number;
    variants?: ProductVariant[];
}

export interface ProductVariant {
    id?: number;
    product_id: number;
    name: string;
    attributes: Record<string, any>; // Example: { color: 'red', size: 'M' }
    sku?: string;
}

export interface Stock {
    warehouse_id: number;
    product_id?: number;
    product_variant_id?: number;
    quantity: number;
}
