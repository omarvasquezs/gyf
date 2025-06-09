<template>
  <div>
    <div class="mb-4">
      <button @click="goBack" class="btn btn-link">← Regresar</button>
    </div>
    <h2>Gestión de Diseños de Luna</h2>
    <button @click="showCreateForm" class="btn btn-primary mb-3">Crear Diseño de Luna</button>
    <button @click="resetFilters" class="btn btn-secondary mb-3 ms-2">Resetear Filtros</button>
    <div v-if="alertMessage" class="alert alert-success alert-dismissible fade show" role="alert">
      {{ alertMessage }}
      <button type="button" class="btn-close" @click="closeAlert" aria-label="Close"></button>
    </div>
    <table class="table table-striped">
      <thead>
        <tr>
          <th>Nombre</th>
          <th>Acciones</th>
        </tr>
        <tr>
          <th>
            <div class="position-relative">
              <input type="text" v-model="filters.nombre" @input="applyFilters" class="form-control" placeholder="Filtrar por Nombre">
              <button v-if="filters.nombre" @click="clearFilter('nombre')" class="btn-clear">
                <img :src="`${baseUrl}/images/close.png`" alt="Clear" class="clear-icon">
              </button>
            </div>
          </th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <tr v-if="disenosLuna && disenosLuna.length === 0">
          <td colspan="2" class="text-center">No hay registros de diseños de luna en el sistema.</td>
        </tr>
        <tr v-else v-for="diseno in disenosLuna" :key="diseno.id">
          <td>{{ diseno.nombre }}</td>
          <td>
            <button @click="editDiseno(diseno)" class="btn btn-warning btn-sm me-2">
              <i class="fas fa-pencil-alt"></i>
            </button>
            <button @click="confirmDelete(diseno.id)" class="btn btn-danger btn-sm">
              <i class="fas fa-trash-alt"></i>
            </button>
          </td>
        </tr>
      </tbody>
    </table>
    <nav v-if="pagination.total > pagination.per_page">
      <ul class="pagination">
        <li class="page-item" :class="{ disabled: !pagination.prev_page_url }">
          <a class="page-link" href="#" @click.prevent="changePage(pagination.prev_page_url)">Anterior</a>
        </li>
        <li class="page-item" :class="{ disabled: !pagination.next_page_url }">
          <a class="page-link" href="#" @click.prevent="changePage(pagination.next_page_url)">Siguiente</a>
        </li>
      </ul>
    </nav>
    <!-- Modal for Viewing Diseno Luna -->
    <div class="modal fade" id="viewDisenoLunaModal" tabindex="-1" aria-labelledby="viewDisenoLunaModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="viewDisenoLunaModalLabel">Información del Diseño de Luna</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="row mb-3">
              <div class="col-sm-3"><strong>Nombre:</strong></div>
              <div class="col-sm-9">{{ selectedDiseno.nombre }}</div>
            </div>
            <div class="row mb-3">
              <div class="col-sm-3"><strong>Creado en:</strong></div>
              <div class="col-sm-9">{{ selectedDiseno.created_at }}</div>
            </div>
            <div class="row mb-3">
              <div class="col-sm-3"><strong>Actualizado en:</strong></div>
              <div class="col-sm-9">{{ selectedDiseno.updated_at }}</div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
          </div>
        </div>
      </div>
    </div>
    <!-- Modal -->
    <div class="modal fade" id="disenoLunaModal" tabindex="-1" aria-labelledby="disenoLunaModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="disenoLunaModalLabel">{{ isEditing ? 'Editar Diseño de Luna' : 'Crear Diseño de Luna' }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="submitForm">
              <div v-if="formErrors.length" class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul>
                  <li v-for="error in formErrors" :key="error">{{ error }}</li>
                </ul>
                <button type="button" class="btn-close" @click="closeFormErrorAlert" aria-label="Close"></button>
              </div>
              <div class="mb-3">
                <label for="nombre" class="form-label">Nombre*:</label>
                <input type="text" v-model="form.nombre" id="nombre" class="form-control" required>
              </div>
              <div class="d-flex justify-content-end gap-2">
                <button type="submit" class="btn btn-primary">{{ isEditing ? 'Actualizar' : 'Guardar' }}</button>
                <button type="button" @click="closeModal" class="btn btn-secondary">Cerrar</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import { Modal } from 'bootstrap';

export default {
  data() {
    return {
      baseUrl: window.location.origin,
      disenosLuna: [],
      form: {
        nombre: ''
      },
      selectedDiseno: {},
      formErrors: [],
      showForm: false,
      isEditing: false,
      editingId: null,
      alertMessage: '',
      pagination: {
        total: 0,
        per_page: 10,
        current_page: 1,
        last_page: 1,
        next_page_url: null,
        prev_page_url: null
      },
      filters: {
        nombre: ''
      }
    };
  },
  methods: {
    fetchDisenosLuna(url = '/api/diseno-lunas') {
      const params = {
        page: this.pagination.current_page,
        per_page: this.pagination.per_page,
        ...this.filters
      };
      axios.get(url, { params })
        .then(response => {
          this.disenosLuna = response.data.data || response.data;
          this.pagination = {
            ...this.pagination,
            total: response.data.total || response.data.length,
            last_page: response.data.last_page || 1,
            next_page_url: response.data.next_page_url || null,
            prev_page_url: response.data.prev_page_url || null
          };
        })
        .catch(error => {
          console.error(error);
        });
    },
    showCreateForm() {
      this.resetForm();
      this.showForm = true;
      const modal = new Modal(document.getElementById('disenoLunaModal'));
      modal.show();
    },
    editDiseno(diseno) {
      this.form = { ...diseno };
      this.showForm = true;
      this.isEditing = true;
      this.editingId = diseno.id;
      const modal = new Modal(document.getElementById('disenoLunaModal'));
      modal.show();
    },
    async submitForm() {
      this.formErrors = [];
      try {
        let response;
        if (this.isEditing) {
          response = await axios.put(`/api/diseno-lunas/${this.editingId}`, this.form);
        } else {
          response = await axios.post('/api/diseno-lunas', this.form);
        }
        this.alertMessage = this.isEditing ? 'Diseño de Luna actualizado con éxito.' : 'Diseño de Luna creado con éxito.';
        this.fetchDisenosLuna();
        this.resetForm();
        this.closeModal();
        setTimeout(() => this.alertMessage = '', 5000);
      } catch (error) {
        if (error.response && error.response.status === 422) {
          this.formErrors = Object.values(error.response.data.errors).flat();
        } else {
          console.error(error);
        }
      }
    },
    confirmDelete(id) {
      if (confirm('¿Está seguro de que desea eliminar este diseño de luna?')) {
        this.deleteDiseno(id);
      }
    },
    async deleteDiseno(id) {
      try {
        await axios.delete(`/api/diseno-lunas/${id}`);
        this.fetchDisenosLuna();
        this.alertMessage = 'Diseño de Luna eliminado con éxito.';
        setTimeout(() => this.alertMessage = '', 5000);
      } catch (error) {
        console.error(error);
      }
    },
    resetForm() {
      this.form = {
        nombre: ''
      };
      this.isEditing = false;
      this.editingId = null;
    },
    closeModal() {
      const modal = Modal.getInstance(document.getElementById('disenoLunaModal'));
      if (modal) {
        modal.hide();
      }
    },
    changePage(url) {
      if (url) {
        const page = new URL(url).searchParams.get('page');
        this.pagination.current_page = page;
        this.fetchDisenosLuna(url);
      }
    },
    applyFilters() {
      this.pagination.current_page = 1;
      this.fetchDisenosLuna();
    },
    resetFilters() {
      this.filters = {
        nombre: ''
      };
      this.applyFilters();
    },
    clearFilter(filter) {
      this.filters[filter] = '';
      this.applyFilters();
    },
    closeAlert() {
      this.alertMessage = '';
    },
    goBack() {
      window.history.back();
    },
    closeFormErrorAlert() {
      this.formErrors = [];
    }
  },
  mounted() {
    this.fetchDisenosLuna();
  }
};
</script>

<style scoped>
.btn-clear {
  background: none;
  border: none;
  color: #6c757d;
  cursor: pointer;
  padding: 0;
  position: absolute;
  right: 10px;
  top: 50%;
  transform: translateY(-50%);
  display: flex;
  align-items: center;
  justify-content: center;
}

.clear-icon {
  width: 12px;
  height: 12px;
  object-fit: contain;
}
</style>
