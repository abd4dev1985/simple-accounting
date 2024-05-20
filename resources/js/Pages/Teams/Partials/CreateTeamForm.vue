<script setup>
import { useForm } from '@inertiajs/vue3';
import FormSection from '@/Components/FormSection.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Calendar from 'primevue/calendar';


const form = useForm({
    name: '',
    StartPeriod:null ,
});

const createTeam = () => {
    form.post(route('teams.store'), {
        errorBag: 'createTeam',
        preserveScroll: true,
    });
};
</script>

<template>
    <FormSection @submitted="createTeam">
        <template #title>
            Create New Project
        </template>

        <template #description>
            Create a new Project and database , Remember to choose Start Period for project.
        </template>

        <template #form>
            <div class="col-span-6">
                <InputLabel value="Project Owner" />

                <div class="flex items-center mt-2">
                    <img class="object-cover w-12 h-12 rounded-full" :src="$page.props.auth.user.profile_photo_url" :alt="$page.props.auth.user.name">

                    <div class="ml-4 leading-tight">
                        <div class="text-gray-900 dark:text-white">{{ $page.props.auth.user.name }}</div>
                        <div class="text-sm text-gray-700 dark:text-gray-300">
                            {{ $page.props.auth.user.email }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-span-6 sm:col-span-4">
                <InputLabel for="name" value="Team Name" />
                <TextInput
                    id="name"
                    v-model="form.name"
                    type="text"
                    class="block w-full mt-1"
                    autofocus
                />
                <InputError :message="form.errors.name" class="mt-2" />
            </div>
            <div class="col-span-6 sm:col-span-4" >
                <InputLabel for="StartPeriod" value="Start Period" />
                <Calendar v-model="form.StartPeriod" showIcon  dateFormat="dd/mm/yy"
                    :pt="{
                        root:{class:' dark:bg-gray-700'},
                        input: { 
                          class: 'bg-white text-center h-8 w-32 dark:bg-gray-700 dark:text-gray-200  focus:ring-2',
                          id:'StartPeriod',
                        },
                        dropdownButton: {
                          root: { class: 'h-8 bg-sky-700' }
                        }
                    }"
                  />
                <InputError :message="form.errors.StartPeriod" class="mt-2" />    
            </div>
        </template>

        <template #actions>
            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                Create
            </PrimaryButton>
        </template>
    </FormSection>
</template>
