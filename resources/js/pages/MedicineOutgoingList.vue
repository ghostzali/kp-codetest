<template>
    <v-container>
        <h1>Medicine Outgoing List</h1>
        <v-data-table
            :headers="headers"
            :items="paginatedData"
            :search="search"
            :pagination.sync="pagination"
            :total-items="totalData"
            :rows-per-page-items="[5, 10, 15]"
            item-key="id"
            :loading="loading"
        >
            <template v-slot:top>
                <v-text-field
                    v-model="search"
                    label="Search"
                    single-line
                ></v-text-field>
            </template>
            <template v-slot:item.action="{ item }">
                <v-icon small @click="editData(item)">mdi-pencil</v-icon>
                <v-icon small @click="deleteData(item)">mdi-delete</v-icon>
            </template>
        </v-data-table>
    </v-container>
</template>

<script lang="ts">
import { ref, computed } from "vue";

export default {
    data() {
        return {
            search: "",
            pagination: {
                sortBy: "",
                descending: true,
                page: 1,
                rowsPerPage: 10,
            },
            headers: [
                { text: "ID", value: "id" },
                // { text: "Name", value: "name" },
                // { text: "Email", value: "email" },
                { text: "Actions", value: "action", sortable: false },
            ],
        };
    },
    setup() {
        const medouts = ref([]);
        const loading = ref(false);

        const fetchData = async () => {
            // Fetch medicine outgoings from API
            loading.value = true;
            try {
                // Simulating API call
                const response = await fetch("/api/v1/en/medicine-outgoings");
                const data = await response.json();
                medouts.value = data;
            } catch (error) {
                console.error("Error fetching medicine outgoings:", error);
            } finally {
                loading.value = false;
            }
        };

        const totalData = computed(() => medouts.value.length);

        const paginatedData = computed(() => {
            const { sortBy, descending, page, rowsPerPage } = this.pagination;
            const start = (page - 1) * rowsPerPage;
            const end = page * rowsPerPage;

            let sortedData = medouts.value.slice();

            if (sortBy) {
                sortedData.sort((a, b) => {
                    const modifier = descending ? -1 : 1;
                    if (a[sortBy] < b[sortBy]) return -1 * modifier;
                    if (a[sortBy] > b[sortBy]) return 1 * modifier;
                    return 0;
                });
            }

            return sortedData.slice(start, end);
        });

        return {
            medouts,
            loading,
            fetchData,
            totalData,
            paginatedData,
        };
    },
    mounted() {
        this.fetchData();
    },
    methods: {
        editData(id) {
            // TODO: Navigate to medicine outgoing edit page
            console.log("Edit Medicine Outgoing:", id);
        },
        deleteData(id) {
            // TODO: Delete medicine outgoing
            console.log("Delete Medicine Outgoing:", id);
        },
    },
};
</script>

<style></style>
