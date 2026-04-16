<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    username: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register.comprador.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Registro de Comprador" />

        <div class="mb-6 text-center">
            <h2 class="text-2xl font-bold text-gray-900">Registro de Comprador</h2>
            <p class="text-gray-600 mt-2">Únete como comprador y compra los mejores productos</p>
            <div class="mt-3 inline-flex items-center px-4 py-2 bg-blue-50 border border-blue-200 rounded-lg">
                <span class="text-blue-800 text-sm">🛒 Tu correo será: <strong class="text-blue-900">{{ username ? username + '@cliente.com' : 'usuario@cliente.com' }}</strong></span>
            </div>
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="name" value="Nombre Completo" />

                <TextInput
                    id="name"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />

                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div class="mt-4">
                <InputLabel for="username" value="Nombre de Usuario" />

                <div class="relative mt-1">
                    <TextInput
                        id="username"
                        type="text"
                        class="mt-1 block w-full pr-32"
                        v-model="form.username"
                        required
                        autocomplete="username"
                        placeholder="ej: juan"
                    />
                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">@cliente.com</span>
                </div>

                <InputError class="mt-2" :message="form.errors.username" />
            </div>

            <div class="mt-4">
                <InputLabel for="password" value="Contraseña" />

                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password"
                    required
                    autocomplete="new-password"
                />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4">
                <InputLabel
                    for="password_confirmation"
                    value="Confirmar Contraseña"
                />

                <TextInput
                    id="password_confirmation"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                />

                <InputError
                    class="mt-2"
                    :message="form.errors.password_confirmation"
                />
            </div>

            <div class="mt-4 flex items-center justify-end">
                <Link
                    :href="route('login')"
                    class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                >
                    ¿Ya tienes cuenta? Inicia sesión
                </Link>

                <PrimaryButton
                    class="ms-4"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Registrarse como Comprador
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
