<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    users: Array,
    currentUserEmail: String
});

const approveForm = useForm({});
const rejectForm = useForm({});
const changeRoleForm = useForm({ role: '' });

const approveUser = (userId) => {
    approveForm.post(route('admin.users.approve', userId), {
        onSuccess: () => {
            approveForm.reset();
        }
    });
};

const rejectUser = (userId) => {
    rejectForm.post(route('admin.users.reject', userId), {
        onSuccess: () => {
            rejectForm.reset();
        }
    });
};

const changeRole = (userId, newRole) => {
    changeRoleForm.role = newRole;
    changeRoleForm.patch(route('admin.users.change-role', userId), {
        onSuccess: () => {
            changeRoleForm.reset();
        }
    });
};

const getRoleBadge = (role) => {
    if (role === 'admin') {
        return 'bg-red-100 text-red-800 border-red-200';
    }
    return 'bg-red-100 text-red-800 border-red-200';
};

const getRoleText = (role) => {
    if (role === 'admin') return 'Administrador';
    return 'Cliente';
};

const getApprovalBadge = (isApproved, role) => {
    if (role !== 'admin') return null;
    if (isApproved) {
        return 'bg-red-100 text-red-800 border-red-200';
    }
    return 'bg-red-100 text-red-800 border-red-200';
};

const getApprovalText = (isApproved, role) => {
    if (role !== 'admin') return null;
    if (isApproved) return 'Aprobado';
    return 'Pendiente';
};
</script>

<template>
    <Head title="Gestión de Usuarios - Administración" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-slate-900 p-8 rounded-3xl shadow-2xl border-b-4 border-red-700">
                <div>
                    <h1 class="text-3xl font-black text-white tracking-tight">Gestión de Usuarios</h1>
                    <p class="text-slate-400 mt-2 font-medium">Administra usuarios y aprueba administradores</p>
                </div>
                <div class="flex gap-3">
                    <span class="px-4 py-2 bg-slate-800 rounded-xl text-red-400 font-black text-sm border border-slate-700">
                        Total: {{ users.length }}
                    </span>
                    <span class="px-4 py-2 bg-slate-800 rounded-xl text-red-400 font-black text-sm border border-slate-700">
                        Admins: {{ users.filter(u => u.role === 'admin').length }}
                    </span>
                    <span class="px-4 py-2 bg-slate-800 rounded-xl text-red-400 font-black text-sm border border-slate-700">
                        Pendientes: {{ users.filter(u => u.role === 'admin' && !u.is_approved).length }}
                    </span>
                </div>
            </div>
        </template>

        <div class="py-10 bg-slate-50 min-h-screen">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-[10px] font-black uppercase tracking-wider text-slate-600">Usuario</th>
                                    <th class="px-6 py-4 text-left text-[10px] font-black uppercase tracking-wider text-slate-600">Email</th>
                                    <th class="px-6 py-4 text-left text-[10px] font-black uppercase tracking-wider text-slate-600">Rol</th>
                                    <th class="px-6 py-4 text-left text-[10px] font-black uppercase tracking-wider text-slate-600">Estado</th>
                                    <th class="px-6 py-4 text-left text-[10px] font-black uppercase tracking-wider text-slate-600">Fecha Registro</th>
                                    <th class="px-6 py-4 text-right text-[10px] font-black uppercase tracking-wider text-slate-600">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-slate-200">
                                <tr v-for="user in users" :key="user.id" class="hover:bg-slate-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 bg-slate-200 rounded-full flex items-center justify-center text-slate-600 font-black text-sm">
                                                {{ user.name.charAt(0).toUpperCase() }}
                                            </div>
                                            <div>
                                                <div class="text-sm font-black text-slate-900">{{ user.name }}</div>
                                                <div class="text-[10px] text-slate-500">ID: {{ user.id }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-slate-700">{{ user.email }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-3 py-1.5 rounded-xl text-[10px] font-black uppercase border tracking-wider inline-block" :class="getRoleBadge(user.role)">
                                            {{ getRoleText(user.role) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span v-if="getApprovalBadge(user.is_approved, user.role)" 
                                              class="px-3 py-1.5 rounded-xl text-[10px] font-black uppercase border tracking-wider inline-block"
                                              :class="getApprovalBadge(user.is_approved, user.role)">
                                            {{ getApprovalText(user.is_approved, user.role) }}
                                        </span>
                                        <span v-else class="text-slate-400 text-[10px] font-medium">N/A</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-slate-600">
                                            {{ new Date(user.created_at).toLocaleDateString('es-ES', { day: '2-digit', month: 'short', year: 'numeric' }) }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center justify-end gap-2">
                                            <select v-if="currentUserEmail === 'admin@admin.com' && user.email !== currentUserEmail"
                                                    v-model="changeRoleForm.role"
                                                    @change="changeRole(user.id, changeRoleForm.role)"
                                                    class="text-[10px] px-2 py-1 border border-slate-200 rounded-lg focus:ring-2 focus:ring-red-700">
                                                <option value="cliente">Cliente</option>
                                                <option value="admin">Admin</option>
                                            </select>
                                            <button v-if="user.role === 'admin' && !user.is_approved && user.email !== currentUserEmail"
                                                    @click="approveUser(user.id)"
                                                    class="px-3 py-1.5 bg-red-700 hover:bg-red-800 text-white text-[10px] font-black rounded-lg transition-colors">
                                                Aprobar
                                            </button>
                                            <button v-if="user.role === 'admin' && user.is_approved && user.email !== currentUserEmail && user.email !== 'admin@admin.com'"
                                                    @click="rejectUser(user.id)"
                                                    class="px-3 py-1.5 bg-red-700 hover:bg-red-800 text-white text-[10px] font-black rounded-lg transition-colors">
                                                Rechazar
                                            </button>
                                            <span v-if="user.email === currentUserEmail" class="text-[10px] text-gray-400 italic">
                                                (Tú)
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
