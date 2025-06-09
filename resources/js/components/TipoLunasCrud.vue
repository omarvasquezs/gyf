<template>
  <div>
    <div class="mb-4">
      <button @click="goBack" class="btn btn-link">← Regresar</button>
    </div>
    <h2>Gestión de Tipos de Luna</h2>
    <button @click="showCreateForm" class="btn btn-primary mb-3">Crear Tipo de Luna</button>
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
        <tr v-if="tiposLuna && tiposLuna.length === 0">
          <td colspan="2" class="text-center">No hay registros de tipos de luna en el sistema.</td>
        </tr>
        <tr v-else v-for="tipo in tiposLuna" :key="tipo.id">
          <td>{{ tipo.nombre }}</td>
          <td>
            <button @click="editTipo(tipo)" class="btn btn-warning btn-sm me-2">
              <i class="fas fa-pencil-alt"></i>
            </button>
            <button @click="confirmDelete(tipo.id)" class="btn btn-danger btn-sm">
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
    <!-- Modal for Viewing Tipo Luna -->
    <div class="modal fade" id="viewTipoLunaModal" tabindex="-1" aria-labelledby="viewTipoLunaModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="viewTipoLunaModalLabel">Información del Tipo de Luna</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="row mb-3">
              <div class="col-sm-3"><strong>Nombre:</strong></div>
              <div class="col-sm-9">{{ selectedTipo.nombre }}</div>
            </div>
            <div class="row mb-3">
              <div class="col-sm-3"><strong>Creado en:</strong></div>
              <div class="col-sm-9">{{ selectedTipo.created_at }}</div>
            </div>
            <div class="row mb-3">
              <div class="col-sm-3"><strong>Actualizado en:</strong></div>
              <div class="col-sm-9">{{ selectedTipo.updated_at }}</div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
          </div>
        </div>
      </div>
    </div>
    <!-- Modal -->
    <div class="modal fade" id="tipoLunaModal" tabindex="-1" aria-labelledby="tipoLunaModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="tipoLunaModalLabel">{{ isEditing ? 'Editar Tipo de Luna' : 'Crear Tipo de Luna' }}</h5>
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
      tiposLuna: [],
      form: {
        nombre: ''
      },
      selectedTipo: {},
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
    fetchTiposLuna(url = '/api/tipo-lunas') {
      const params = {
        page: this.pagination.current_page,
        per_page: this.pagination.per_page,
        ...this.filters
      };
      axios.get(url, { params })
        .then(response => {
          this.tiposLuna = response.data.data || response.data;
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
      const modal = new Modal(document.getElementById('tipoLunaModal'));
      modal.show();
    },
    editTipo(tipo) {
      this.form = { ...tipo };
      this.showForm = true;
      this.isEditing = true;
      this.editingId = tipo.id;
      const modal = new Modal(document.getElementById('tipoLunaModal'));
      modal.show();
    },
    async submitForm() {
      this.formErrors = [];
      try {
        let response;
        if (this.isEditing) {
          response = await axios.put(`/api/tipo-lunas/${this.editingId}`, this.form);
        } else {
          response = await axios.post('/api/tipo-lunas', this.form);
        }
        this.alertMessage = this.isEditing ? 'Tipo de Luna actualizado con éxito.' : 'Tipo de Luna creado con éxito.';
        this.fetchTiposLuna();
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
      if (confirm('¿Está seguro de que desea eliminar este tipo de luna?')) {
        this.deleteTipo(id);
      }
    },
    async deleteTipo(id) {
      try {
        await axios.delete(`/api/tipo-lunas/${id}`);
        this.fetchTiposLuna();
        this.alertMessage = 'Tipo de Luna eliminado con éxito.';
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
      const modal = Modal.getInstance(document.getElementById('tipoLunaModal'));
      if (modal) {
        modal.hide();
      }
    },
    changePage(url) {
      if (url) {
        const page = new URL(url).searchParams.get('page');
        this.pagination.current_page = page;
        this.fetchTiposLuna(url);
      }
    },
    applyFilters() {
      this.pagination.current_page = 1;
      this.fetchTiposLuna();
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
    this.fetchTiposLuna();
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
