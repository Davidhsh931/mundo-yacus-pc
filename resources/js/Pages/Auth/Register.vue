<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Register" />

        <form @submit.prevent="submit" class="max-w-sm mx-auto w-full px-6">
            <div class="bg-white/95 backdrop-blur-sm rounded-2xl shadow-2xl p-6 border border-white/20">
                <div class="text-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-900">Crear Cuenta</h2>
                    <p class="text-sm text-gray-600 mt-1">Únete a Mundo Yacus</p>
                </div>

                <div class="space-y-4">
                    <div>
                        <InputLabel for="name" value="Nombre completo" class="mb-1 text-sm font-medium text-gray-700" />

                        <TextInput
                            id="name"
                            type="text"
                            class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-lg"
                            v-model="form.name"
                            required
                            autofocus
                            autocomplete="name"
                            placeholder="Tu nombre completo"
                        />

                        <InputError class="mt-2" :message="form.errors.name" />
                    </div>

                    <div>
                        <InputLabel for="email" value="Correo electrónico" class="mb-1 text-sm font-medium text-gray-700" />

                        <TextInput
                            id="email"
                            type="email"
                            class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-lg"
                            v-model="form.email"
                            required
                            autocomplete="username"
                            placeholder="tu@email.com"
                        />

                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <div>
                        <InputLabel for="password" value="Contraseña" class="mb-1 text-sm font-medium text-gray-700" />

                        <TextInput
                            id="password"
                            type="password"
                            class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-lg"
                            v-model="form.password"
                            required
                            autocomplete="new-password"
                            placeholder="••••••••"
                        />

                        <InputError class="mt-2" :message="form.errors.password" />
                    </div>

                    <div>
                        <InputLabel
                            for="password_confirmation"
                            value="Confirmar contraseña"
                            class="mb-1 text-sm font-medium text-gray-700"
                        />

                        <TextInput
                            id="password_confirmation"
                            type="password"
                            class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-lg"
                            v-model="form.password_confirmation"
                            required
                            autocomplete="new-password"
                            placeholder="••••••••"
                        />

                        <InputError
                            class="mt-2"
                            :message="form.errors.password_confirmation"
                        />
                    </div>
                </div>

                <div class="mt-6 space-y-3">
                    <Link
                        :href="route('login')"
                        class="block text-center text-sm text-gray-600 hover:text-blue-600 transition-colors"
                    >
                        ¿Ya tienes cuenta? Inicia sesión
                    </Link>

                    <PrimaryButton
                        class="w-full bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold py-3 rounded-lg transition-all shadow-lg hover:shadow-xl"
                        :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                        :disabled="form.processing"
                    >
                        <span v-if="form.processing">Registrando...</span>
                        <span v-else>Registrarse</span>
                    </PrimaryButton>
                </div>
            </div>
        </form>
    </GuestLayout>
</template>
