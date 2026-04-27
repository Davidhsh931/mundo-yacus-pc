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

        <form @submit.prevent="submit" class="max-w-md mx-auto w-full px-6">
            <div class="bg-white rounded-2xl shadow-lg p-8">
                <div>
                    <InputLabel for="name" value="Name" class="mb-1" />

                    <TextInput
                        id="name"
                        type="text"
                        class="mt-1 block w-full border-gray-300"
                        v-model="form.name"
                        required
                        autofocus
                        autocomplete="name"
                    />

                    <InputError class="mt-2" :message="form.errors.name" />
                </div>

                <div class="mt-4">
                    <InputLabel for="email" value="Email" class="mb-1" />

                    <TextInput
                        id="email"
                        type="email"
                        class="mt-1 block w-full border-gray-300"
                        v-model="form.email"
                        required
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
                        autocomplete="new-password"
                    />

                    <InputError class="mt-2" :message="form.errors.password" />
                </div>

                <div class="mt-4">
                    <InputLabel
                        for="password_confirmation"
                        value="Confirm Password"
                        class="mb-1"
                    />

                    <TextInput
                        id="password_confirmation"
                        type="password"
                        class="mt-1 block w-full border-gray-300"
                        v-model="form.password_confirmation"
                        required
                        autocomplete="new-password"
                    />

                    <InputError
                        class="mt-2"
                        :message="form.errors.password_confirmation"
                    />
                </div>

                <div class="mt-6 space-y-3">
                    <Link
                        :href="route('login')"
                        class="block text-center text-sm text-gray-600 underline hover:text-gray-900"
                    >
                        Already registered?
                    </Link>

                    <PrimaryButton
                        class="w-full"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                    >
                        Register
                    </PrimaryButton>
                </div>
            </div>
        </form>
    </GuestLayout>
</template>
