<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    // Cambiamos route('login') por el string directo '/login'
    form.post('/login', {
        onFinish: () => form.reset('password'),
        onSuccess: () => console.log("¡Éxito!"),
        onError: (errors) => console.log("Errores:", errors),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Log in" />

        <form @submit.prevent="submit" class="max-w-md mx-auto w-full px-6">
            <div class="bg-white rounded-2xl shadow-lg p-8">
                <div v-if="status" class="mb-4 text-sm font-medium text-green-600">
                    {{ status }}
                </div>

                <div>
                    <InputLabel for="email" value="Email" class="mb-1" />

                    <TextInput
                        id="email"
                        type="email"
                        class="mt-1 block w-full border-gray-300"
                        v-model="form.email"
                        required
                        autofocus
                        autocomplete="username"
                    />

                    <InputError class="mt-2" :message="form.errors.email" />
                </div>

                <div class="mt-4">
                    <InputLabel for="password" value="Password" class="mb-1" />

                    <TextInput
                        id="password"
                        type="password"
                        class="mt-1 block w-full border-gray-300"
                        v-model="form.password"
                        required
                        autocomplete="current-password"
                    />

                    <InputError class="mt-2" :message="form.errors.password" />
                </div>

                <div class="mt-4 block">
                    <label class="flex items-center">
                        <Checkbox name="remember" v-model:checked="form.remember" />
                        <span class="ms-2 text-sm text-gray-600"
                            >Remember me</span
                        >
                    </label>
                </div>

                <div class="mt-6 space-y-3">
                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="block text-center text-sm text-gray-600 underline hover:text-gray-900"
                    >
                        Forgot your password?
                    </Link>

                    <PrimaryButton
                        class="w-full"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                    >
                        Log in
                    </PrimaryButton>
                </div>
            </div>
        </form>
    </GuestLayout>
</template>
